<?php

namespace App\Nova\Actions;

use App\Http\Controllers\API\v1\StoreController;
use App\Http\Requests\VerifyReceiptRequest;
use App\Models\ReconciliationRow;
use App\Models\ReconciliationUserImpact;
use App\Models\User;
use AppStoreServerLibrary\AppStoreServerAPIClient\APIException;
use AppStoreServerLibrary\AppStoreServerAPIClient\GetTransactionHistoryVersion;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use RuntimeException;
use Throwable;

class ApplyReconciliationImpact extends Action
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Apply missing transactions';

    /**
     * Confirmation message shown before execution.
     *
     * @var string
     */
    public $confirmText = "Re-fetches each selected user's Apple transaction history and passes the signed JWSes through POST /v1/store/verify. Writes are additive (users.is_pro is sticky).";

    /**
     * Button labels.
     *
     * @var string
     */
    public $confirmButtonText = 'Apply';

    /**
     * @var string
     */
    public $cancelButtonText = 'Cancel';

    /**
     * VerifyReceiptRequest's transactions.* rule caps at 50 per call.
     */
    private const int VERIFY_CHUNK = 50;

    /**
     * Replay one or more users' Apple history through StoreController::verifyReceipt.
     */
    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        $applied = 0;
        $failed = 0;
        $firstError = null;

        foreach ($models as $impact) {
            /** @var ReconciliationUserImpact $impact */
            try {
                $this->applyOne($impact);
                $applied++;
            } catch (Throwable $e) {
                $failed++;
                $firstError ??= sprintf('%s: %s', $impact->user_id, $e->getMessage());

                logger()->warning('ApplyReconciliationImpact failed.', [
                    'impact_id' => $impact->id,
                    'user_id' => $impact->user_id,
                    'error' => $e->getMessage(),
                ]);

                $impact->update([
                    'applied_at' => now(),
                    'applied_error' => mb_substr($e->getMessage(), 0, 255),
                ]);
            }
        }

        if ($failed > 0) {
            return Action::danger(sprintf('Applied %d, failed %d. First error: %s', $applied, $failed, $firstError));
        }

        return Action::message(sprintf('Applied transactions for %d user(s).', $applied));
    }

    /**
     * Fetch fresh JWSes for one impact row's user and push them through verifyReceipt in chunks of 50.
     */
    private function applyOne(ReconciliationUserImpact $impact): void
    {
        $run = $impact->reconciliationRun;
        if (!$run) {
            throw new RuntimeException('Impact row has no run.');
        }

        $user = User::where('uuid', $impact->user_id)->first();
        if (!$user) {
            throw new RuntimeException('User not found.');
        }

        $anchor = ReconciliationRow::where('reconciliation_run_id', $run->id)
            ->where('user_id', $user->uuid)
            ->where('status', ReconciliationRow::STATUS_MISSING)
            ->where('source', ReconciliationRow::SOURCE_HISTORY)
            ->whereNotNull('original_transaction_id')
            ->value('original_transaction_id');

        if (!$anchor) {
            throw new RuntimeException('No missing-transaction anchor found for user.');
        }

        $signed = $this->fetchSignedTransactions($anchor, $run->environment);

        if (empty($signed)) {
            $impact->update([
                'applied_at' => now(),
                'applied_count' => 0,
                'applied_pro' => (bool) $user->is_pro,
                'applied_plus' => (bool) $user->is_subscribed,
                'applied_error' => null,
            ]);
            return;
        }

        // Swap the guard user in-memory only — do NOT call login()/logout(), which would rotate the
        // admin's session cookie and sign them out of Nova.
        $previousUser = auth()->user();
        auth()->setUser($user);

        try {
            foreach (array_chunk($signed, self::VERIFY_CHUNK) as $chunk) {
                $request = $this->buildVerifyRequest($chunk, $run->environment);
                app(StoreController::class)->verifyReceipt($request);
            }
        } finally {
            auth()->setUser($previousUser);
        }

        $user->refresh();

        $impact->update([
            'applied_at' => now(),
            'applied_count' => count($signed),
            'applied_pro' => (bool) $user->is_pro,
            'applied_plus' => (bool) $user->is_subscribed,
            'applied_error' => null,
        ]);
    }

    /**
     * Paginate Apple's transaction history for the anchor and return every signed JWS.
     *
     * @return list<string>
     */
    private function fetchSignedTransactions(string $anchor, ?string $env): array
    {
        $all = [];
        $revision = null;

        do {
            try {
                $response = appStore($env)->getTransactionHistory(
                    transactionId: $anchor,
                    revision: $revision,
                    version: GetTransactionHistoryVersion::V2,
                );
            } catch (APIException $e) {
                throw new RuntimeException(sprintf(
                    'Apple getTransactionHistory failed (%d, %s).',
                    $e->getHttpStatusCode(),
                    $e->getApiError()?->name ?? 'unknown',
                ), previous: $e);
            }

            foreach ($response->getSignedTransactions() ?? [] as $jws) {
                $all[] = $jws;
            }

            $revision = $response->getRevision();
            usleep(100_000);
        } while ($response->getHasMore() === true);

        return $all;
    }

    /**
     * Construct a VerifyReceiptRequest pre-populated with the signed chunk, and prime its validator so
     * verifyReceipt's call to $request->validated() returns the data — without tripping the FormRequest
     * resolver's authorize()/auth side effects.
     *
     * @param  list<string>  $signedTransactions
     */
    private function buildVerifyRequest(array $signedTransactions, ?string $environment): VerifyReceiptRequest
    {
        $parameters = ['transactions' => $signedTransactions];
        if ($environment !== null && $environment !== '') {
            $parameters['environment'] = $environment;
        }

        $request = VerifyReceiptRequest::create(
            uri: '/v1/store/verify',
            method: 'POST',
            parameters: $parameters,
        );

        $request->setContainer(app())->setRedirector(app('redirect'));

        $validator = app(ValidationFactory::class)->make(
            $request->all(),
            $request->rules(),
        );
        $validator->validate();

        $request->setValidator($validator);

        return $request;
    }
}
