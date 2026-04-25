<?php

namespace App\Console\Commands\Importers\AppStore;

use App\Models\AppStoreNotification;
use App\Models\ReconciliationRow;
use App\Models\ReconciliationRun;
use App\Models\UserReceiptTransaction;
use App\Services\ReconciliationSimulator;
use AppStoreServerLibrary\AppStoreServerAPIClient\APIException;
use AppStoreServerLibrary\AppStoreServerAPIClient\GetTransactionHistoryVersion;
use AppStoreServerLibrary\Models\JWSTransactionDecodedPayload;
use AppStoreServerLibrary\Models\NotificationHistoryRequest;
use AppStoreServerLibrary\SignedDataVerifier\VerificationException;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class ReconcileTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iap:reconcile_transactions
        {--user-id= : Restrict to a single user UUID}
        {--env= : Force `production` or `sandbox` for Apple calls}
        {--source=both : history | notifications | both}
        {--since= : ISO date (default: now -30d); only used by notifications}
        {--output= : Optional CSV path for per-row diff rows}
        {--chunk=200 : Users per progress-bar chunk (history source)}
        {--no-simulate : Skip the entitlement before/after simulator at the end}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dry-run reconciliation between local StoreKit state and the Apple App Store Server API.';

    /**
     * Backoff delays in seconds for retrying Apple calls before giving up.
     *
     * @var list<int>
     */
    private const array BACKOFF_SECONDS = [0, 1, 3, 10];

    /**
     * Inter-page pause to stay well under Apple's ~600 req/min ceiling.
     *
     * @var int
     */
    private const int PAGE_DELAY_MICROS = 100_000;

    private int $apiCalls = 0;

    private int $retries = 0;

    private int $rateLimitHits = 0;

    private int $erroredUsers = 0;

    /**
     * Reconciliation run record being populated during this invocation.
     */
    private ?ReconciliationRun $run = null;

    /**
     * CSV handle while --output is in use.
     *
     * @var resource|null
     */
    private $csvHandle = null;

    /**
     * Counters tracked for the run record.
     *
     * @var array<string, int>
     */
    private array $counters = [
        'users_total' => 0,
        'users_with_anchors' => 0,
        'users_skipped' => 0,
        'apple_transactions' => 0,
        'local_present' => 0,
        'local_missing' => 0,
        'local_orphan' => 0,
        'notifications_total' => 0,
        'notifications_present' => 0,
        'notifications_missing' => 0,
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $source = (string) $this->option('source');
        if (!in_array($source, ['history', 'notifications', 'both'], true)) {
            $this->error(sprintf('Invalid --source=%s (expected history|notifications|both).', $source));
            return Command::FAILURE;
        }

        $env = $this->normaliseEnv($this->option('env'));
        if ($env === false) {
            $this->error(sprintf('Invalid --env=%s (expected production|sandbox).', $this->option('env')));
            return Command::FAILURE;
        }

        if (!$this->openCsv()) {
            return Command::FAILURE;
        }

        $this->run = ReconciliationRun::create([
            'run_uuid' => (string) Str::uuid(),
            'source' => $source,
            'environment' => $env,
            'user_id_filter' => $this->option('user-id') ?: null,
            'started_at' => now(),
        ]);

        $this->info(sprintf('Reconciliation run: %s', $this->run->run_uuid));

        try {
            if ($source === 'history' || $source === 'both') {
                $this->runHistory($env);
            }

            if ($source === 'notifications' || $source === 'both') {
                $this->runNotifications($env);
            }

            $this->line('');
            $this->info('Apple API');
            $this->info('---------');
            $this->info(sprintf(
                'Calls: %s   Retries: %s   Rate-limit hits: %s',
                number_format($this->apiCalls),
                number_format($this->retries),
                number_format($this->rateLimitHits),
            ));

            if (!$this->option('no-simulate')) {
                $this->line('');
                $this->info('Simulating entitlement before/after for users with missing transactions...');
                ReconciliationSimulator::simulate($this->run->refresh());
                $this->info(sprintf('Simulated %s users.', number_format($this->run->userImpacts()->count())));
            }
        } finally {
            if ($this->csvHandle !== null) {
                fclose($this->csvHandle);
                $this->csvHandle = null;
            }

            if ($this->run !== null) {
                $this->run->fill($this->counters + [
                    'users_errored' => $this->erroredUsers,
                    'api_calls' => $this->apiCalls,
                    'api_retries' => $this->retries,
                    'rate_limit_hits' => $this->rateLimitHits,
                    'completed_at' => now(),
                ])->save();

                $this->line('');
                $this->info(sprintf('Run %s saved.', $this->run->run_uuid));
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Walk every anchor's transaction history and diff against local state.
     */
    private function runHistory(?string $env): void
    {
        [$anchors, $totalUsers] = $this->discoverAnchors();
        $anchorCount = $anchors->count();
        $skipped = max(0, $totalUsers - $anchorCount);

        $this->counters['users_total'] = $totalUsers;
        $this->counters['users_with_anchors'] = $anchorCount;
        $this->counters['users_skipped'] = $skipped;

        $this->line('');
        $this->info('History source');
        $this->info('--------------');
        $this->info(sprintf('Users with anchors        %s of %s', number_format($anchorCount), number_format($totalUsers)));
        $this->info(sprintf('Users skipped (no anchor) %s', number_format($skipped)));

        if ($anchorCount === 0) {
            return;
        }

        $bar = $this->output->createProgressBar($anchorCount);
        $bar->start();

        $chunkSize = max(1, (int) $this->option('chunk'));

        foreach ($anchors->chunk($chunkSize) as $chunk) {
            foreach ($chunk as $row) {
                $this->diffUser((string) $row->user_id, (string) $row->anchor, $env);
                $bar->advance();
            }
        }

        $bar->finish();
        $this->line('');
        $this->info(sprintf('Users errored (Apple 4xx) %s', number_format($this->erroredUsers)));
        $this->info(sprintf('Transactions on Apple     %s', number_format($this->counters['apple_transactions'])));
        $this->info(sprintf('  local_present           %s', number_format($this->counters['local_present'])));
        $this->info(sprintf('  local_missing           %s   <- backfill target', number_format($this->counters['local_missing'])));
        $this->info(sprintf('  local_orphan            %s', number_format($this->counters['local_orphan'])));
    }

    /**
     * Diff one user's Apple-side transactions against local state.
     */
    private function diffUser(string $userId, string $anchor, ?string $env): void
    {
        $applePayloads = $this->walkTransactionHistory($anchor, $env);
        if ($applePayloads === null) {
            return;
        }

        $appleByTxId = [];
        foreach ($applePayloads as $payload) {
            $txId = $payload->getTransactionId();
            if ($txId === null) {
                continue;
            }
            $appleByTxId[$txId] = $payload;
        }

        $localTxIds = UserReceiptTransaction::where('user_id', $userId)
            ->pluck('transaction_id')
            ->all();
        $localSet = array_flip($localTxIds);

        $rowsToInsert = [];

        foreach ($appleByTxId as $txId => $payload) {
            $status = isset($localSet[$txId]) ? ReconciliationRow::STATUS_PRESENT : ReconciliationRow::STATUS_MISSING;
            $this->counters['apple_transactions']++;
            $this->counters[$status === ReconciliationRow::STATUS_PRESENT ? 'local_present' : 'local_missing']++;
            $rowsToInsert[] = $this->newRow(
                source: ReconciliationRow::SOURCE_HISTORY,
                userId: $userId,
                originalTransactionId: $payload->getOriginalTransactionId(),
                transactionId: $txId,
                productId: $payload->getProductId(),
                notificationUuid: null,
                status: $status,
                payload: self::extractTransactionPayload($payload),
            );
        }

        foreach ($localTxIds as $txId) {
            if (!isset($appleByTxId[$txId])) {
                $this->counters['local_orphan']++;
                $rowsToInsert[] = $this->newRow(
                    source: ReconciliationRow::SOURCE_HISTORY,
                    userId: $userId,
                    originalTransactionId: null,
                    transactionId: $txId,
                    productId: null,
                    notificationUuid: null,
                    status: ReconciliationRow::STATUS_ORPHAN,
                    payload: null,
                );
            }
        }

        if ($rowsToInsert) {
            DB::table(ReconciliationRow::TABLE_NAME)->insert($rowsToInsert);
        }
    }

    /**
     * Discover per-user anchor original_transaction_ids across the three owner tables.
     *
     * @return array{0: Collection, 1: int}
     */
    private function discoverAnchors(): array
    {
        $userIdFilter = $this->option('user-id');

        $transactions = DB::table('user_receipt_transactions')
            ->select('user_id', 'original_transaction_id')
            ->whereNotNull('user_id')
            ->whereNotNull('original_transaction_id');

        $receipts = DB::table('user_receipts')
            ->select('user_id', 'original_transaction_id')
            ->whereNotNull('user_id');

        $consumables = DB::table('consumable_purchases as cp')
            ->join('user_receipt_transactions as urt', 'urt.transaction_id', '=', 'cp.transaction_id')
            ->select('cp.user_id', 'urt.original_transaction_id')
            ->whereNotNull('cp.user_id')
            ->whereNotNull('urt.original_transaction_id');

        if ($userIdFilter) {
            $transactions->where('user_id', $userIdFilter);
            $receipts->where('user_id', $userIdFilter);
            $consumables->where('cp.user_id', $userIdFilter);
        }

        $union = $transactions->union($receipts)->union($consumables);

        $anchors = DB::query()
            ->fromSub($union, 'anchors')
            ->selectRaw('user_id, MIN(original_transaction_id) AS anchor')
            ->groupBy('user_id')
            ->get();

        return [$anchors, $this->countDistinctUsers($userIdFilter)];
    }

    /**
     * Count every user that has any StoreKit-owned row, anchor-reachable or not.
     */
    private function countDistinctUsers(?string $userIdFilter): int
    {
        $q1 = DB::table('user_receipt_transactions')->select('user_id')->whereNotNull('user_id');
        $q2 = DB::table('user_receipts')->select('user_id')->whereNotNull('user_id');
        $q3 = DB::table('consumable_purchases')->select('user_id')->whereNotNull('user_id');

        if ($userIdFilter) {
            $q1->where('user_id', $userIdFilter);
            $q2->where('user_id', $userIdFilter);
            $q3->where('user_id', $userIdFilter);
        }

        return (int) DB::query()
            ->fromSub($q1->union($q2)->union($q3), 'users')
            ->distinct()
            ->count('user_id');
    }

    /**
     * Paginate Apple's transaction history for a single anchor.
     *
     * @return list<JWSTransactionDecodedPayload>|null
     */
    private function walkTransactionHistory(string $anchor, ?string $env): ?array
    {
        $payloads = [];
        $revision = null;

        do {
            try {
                $response = $this->callWithRetry(fn () => appStore($env)->getTransactionHistory(
                    transactionId: $anchor,
                    revision: $revision,
                    version: GetTransactionHistoryVersion::V2,
                ));
            } catch (APIException $e) {
                $this->erroredUsers++;
                logger()->warning('iap:reconcile_transactions history anchor rejected by Apple.', [
                    'anchor' => $anchor,
                    'env' => $env,
                    'http_status' => $e->getHttpStatusCode(),
                    'api_error' => $e->getApiError()?->name,
                    'message' => $e->getErrorMessage(),
                ]);
                return null;
            }

            if ($response === null) {
                $this->erroredUsers++;
                logger()->warning('iap:reconcile_transactions history page failed after retries.', [
                    'anchor' => $anchor,
                    'env' => $env,
                ]);
                return null;
            }

            foreach ($response->getSignedTransactions() ?? [] as $jws) {
                try {
                    $payloads[] = appStoreVerifier($env)->verifyAndDecodeSignedTransaction($jws);
                } catch (VerificationException $e) {
                    logger()->warning('iap:reconcile_transactions signed transaction rejected.', [
                        'anchor' => $anchor,
                        'status' => $e->getStatus()->name,
                    ]);
                }
            }

            $revision = $response->getRevision();
            usleep(self::PAGE_DELAY_MICROS);
        } while ($response->getHasMore() === true);

        return $payloads;
    }

    /**
     * Diff Apple's notification history window against local app_store_notifications.
     */
    private function runNotifications(?string $env): void
    {
        $since = $this->option('since')
            ? Carbon::parse((string) $this->option('since'))
            : now()->subDays(30);
        $end = now();

        if ($this->run !== null) {
            $this->run->fill(['since' => $since, 'until' => $end])->save();
        }

        $request = new NotificationHistoryRequest(
            startDate: $since->getTimestamp() * 1000,
            endDate: $end->getTimestamp() * 1000,
        );

        $this->line('');
        $this->info(sprintf('Notification source (since %s -> %s)', $since->toDateString(), $end->toDateString()));
        $this->info('---------------------------------------------------');

        $rows = [];
        $paginationToken = null;

        do {
            try {
                $response = $this->callWithRetry(
                    fn () => appStore($env)->getNotificationHistory($paginationToken, $request),
                );
            } catch (APIException $e) {
                logger()->warning('iap:reconcile_transactions notification page rejected by Apple.', [
                    'env' => $env,
                    'http_status' => $e->getHttpStatusCode(),
                    'api_error' => $e->getApiError()?->name,
                    'message' => $e->getErrorMessage(),
                ]);
                break;
            }

            if ($response === null) {
                logger()->warning('iap:reconcile_transactions notification page failed after retries.', [
                    'env' => $env,
                ]);
                break;
            }

            foreach ($response->getNotificationHistory() ?? [] as $item) {
                $signed = $item->getSignedPayload();
                if (!$signed) {
                    continue;
                }

                try {
                    $decoded = appStoreVerifier($env)->verifyAndDecodeNotification($signed);
                } catch (VerificationException) {
                    continue;
                }

                $uuid = $decoded->getNotificationUUID();
                if (!$uuid) {
                    continue;
                }

                $row = ['transactionId' => null, 'originalTransactionId' => null, 'productId' => null];
                if (($data = $decoded->getData()) !== null && ($signedTx = $data->getSignedTransactionInfo()) !== null) {
                    try {
                        $tx = appStoreVerifier($env)->verifyAndDecodeSignedTransaction($signedTx);
                        $row['transactionId'] = $tx->getTransactionId();
                        $row['originalTransactionId'] = $tx->getOriginalTransactionId();
                        $row['productId'] = $tx->getProductId();
                    } catch (VerificationException) {
                        // UUID-level diff still works without tx metadata.
                    }
                }

                $rows[$uuid] = $row;
            }

            $paginationToken = $response->getPaginationToken();
            usleep(self::PAGE_DELAY_MICROS);
        } while (!empty($paginationToken));

        $known = AppStoreNotification::whereIn('notification_uuid', array_keys($rows))
            ->pluck('notification_uuid')
            ->all();
        $knownSet = array_flip($known);

        $rowsToInsert = [];
        $present = 0;
        $missing = 0;

        foreach ($rows as $uuid => $meta) {
            $status = isset($knownSet[$uuid]) ? ReconciliationRow::STATUS_PRESENT : ReconciliationRow::STATUS_MISSING;
            $status === ReconciliationRow::STATUS_PRESENT ? $present++ : $missing++;
            $rowsToInsert[] = $this->newRow(
                source: ReconciliationRow::SOURCE_NOTIFICATIONS,
                userId: null,
                originalTransactionId: $meta['originalTransactionId'],
                transactionId: $meta['transactionId'],
                productId: $meta['productId'],
                notificationUuid: $uuid,
                status: $status,
                payload: null,
            );
        }

        if ($rowsToInsert) {
            foreach (array_chunk($rowsToInsert, 500) as $batch) {
                DB::table(ReconciliationRow::TABLE_NAME)->insert($batch);
            }
        }

        $this->counters['notifications_total'] = count($rows);
        $this->counters['notifications_present'] = $present;
        $this->counters['notifications_missing'] = $missing;

        $this->info(sprintf('Notifications on Apple    %s', number_format(count($rows))));
        $this->info(sprintf('  local_present           %s', number_format($present)));
        $this->info(sprintf('  local_missing           %s   <- webhook gaps', number_format($missing)));
    }

    /**
     * Retry Apple calls with bounded exponential backoff on 429/5xx, returning null on exhaustion.
     */
    private function callWithRetry(callable $fn): mixed
    {
        $last = null;

        foreach (self::BACKOFF_SECONDS as $delay) {
            if ($delay > 0) {
                sleep($delay);
                $this->retries++;
            }

            try {
                $result = $fn();
                $this->apiCalls++;
                return $result;
            } catch (APIException $e) {
                $status = $e->getHttpStatusCode();
                if ($status === 429) {
                    $this->rateLimitHits++;
                }
                if ($status !== 429 && $status < 500) {
                    throw $e;
                }
                $last = $e;
            } catch (Throwable $e) {
                $last = $e;
            }
        }

        logger()->warning('iap:reconcile_transactions Apple API exhausted retries.', [
            'error' => $last?->getMessage(),
        ]);

        return null;
    }

    /**
     * Normalise --env into the value expected by Environment::from, or return false for an invalid input.
     *
     * @return string|null|false
     */
    private function normaliseEnv(?string $option): string|null|false
    {
        if ($option === null || $option === '') {
            return null;
        }

        $value = ucfirst(strtolower($option));

        return in_array($value, ['Production', 'Sandbox'], true) ? $value : false;
    }

    /**
     * Open the CSV output sink and write the header row.
     */
    private function openCsv(): bool
    {
        $path = $this->option('output');
        if (!$path) {
            return true;
        }

        $handle = @fopen($path, 'w');
        if ($handle === false) {
            $this->error(sprintf('Could not open %s for writing.', $path));
            return false;
        }

        fputcsv($handle, [
            'source',
            'user_id',
            'original_transaction_id',
            'transaction_id',
            'product_id',
            'notification_uuid',
            'status',
        ]);

        $this->csvHandle = $handle;
        return true;
    }

    /**
     * Build a reconciliation_rows insert payload, and tee the record to the optional CSV sink.
     *
     * @param  array<string, mixed>|null  $payload
     * @return array<string, mixed>
     */
    private function newRow(
        string $source,
        ?string $userId,
        ?string $originalTransactionId,
        ?string $transactionId,
        ?string $productId,
        ?string $notificationUuid,
        string $status,
        ?array $payload,
    ): array {
        if ($this->csvHandle !== null) {
            fputcsv($this->csvHandle, [
                $source,
                $userId,
                $originalTransactionId,
                $transactionId,
                $productId,
                $notificationUuid,
                $status,
            ]);
        }

        $now = now();

        return [
            'reconciliation_run_id' => $this->run?->id,
            'source' => $source,
            'user_id' => $userId,
            'original_transaction_id' => $originalTransactionId,
            'transaction_id' => $transactionId,
            'product_id' => $productId,
            'notification_uuid' => $notificationUuid,
            'status' => $status,
            'payload' => $payload !== null ? json_encode($payload) : null,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * Pluck the minimal decoded-payload fields the simulator needs from a verified JWS.
     *
     * @return array<string, mixed>
     */
    private static function extractTransactionPayload(JWSTransactionDecodedPayload $p): array
    {
        return [
            'transactionId' => $p->getTransactionId(),
            'originalTransactionId' => $p->getOriginalTransactionId(),
            'productId' => $p->getProductId(),
            'purchaseDate' => $p->getPurchaseDate(),
            'expiresDate' => $p->getExpiresDate(),
            'revocationDate' => $p->getRevocationDate(),
        ];
    }
}
