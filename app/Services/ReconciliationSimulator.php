<?php

namespace App\Services;

use App\Enums\StoreProductType;
use App\Models\ConsumablePurchase;
use App\Models\ReconciliationRow;
use App\Models\ReconciliationRun;
use App\Models\ReconciliationUserImpact;
use App\Models\StoreProduct;
use App\Models\User;
use App\Models\UserEntitlement;
use App\Models\UserReceipt;
use App\Models\UserReceiptTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Throwable;

final class ReconciliationSimulator
{
    /**
     * Populate reconciliation_user_impacts for each user with `local_missing` history rows in the run.
     */
    public static function simulate(ReconciliationRun $run): void
    {
        $missingByUser = $run->rows()
            ->where('status', ReconciliationRow::STATUS_MISSING)
            ->where('source', ReconciliationRow::SOURCE_HISTORY)
            ->whereNotNull('user_id')
            ->get()
            ->groupBy('user_id');

        if ($missingByUser->isEmpty()) {
            return;
        }

        $products = StoreProduct::all()->keyBy('product_id');

        foreach ($missingByUser as $userId => $rows) {
            $user = User::where('uuid', $userId)->first();

            if (!$user) {
                ReconciliationUserImpact::updateOrCreate([
                    'reconciliation_run_id' => $run->id,
                    'user_id' => $userId,
                ], [
                    'missing_transactions' => $rows->count(),
                    'error' => 'User not found.',
                ]);
                continue;
            }

            try {
                $impact = self::simulateUser($user, $rows, $products);
            } catch (Throwable $e) {
                logger()->warning('ReconciliationSimulator failed for a user.', [
                    'run_id' => $run->id,
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);

                ReconciliationUserImpact::updateOrCreate([
                    'reconciliation_run_id' => $run->id,
                    'user_id' => $userId,
                ], [
                    'missing_transactions' => $rows->count(),
                    'error' => mb_substr($e->getMessage(), 0, 255),
                ]);
                continue;
            }

            ReconciliationUserImpact::updateOrCreate([
                'reconciliation_run_id' => $run->id,
                'user_id' => $userId,
            ], $impact);
        }
    }

    /**
     * Build before/after entitlement snapshots for one user.
     *
     * @param  Collection<int, ReconciliationRow>  $missingRows
     * @param  Collection<string, StoreProduct>  $products
     * @return array<string, mixed>
     */
    private static function simulateUser(User $user, Collection $missingRows, Collection $products): array
    {
        $currentReceipts = UserReceipt::with('storeProduct')
            ->where('user_id', $user->uuid)
            ->get();
        $currentTransactions = UserReceiptTransaction::where('user_id', $user->uuid)->get();
        $currentConsumables = ConsumablePurchase::with('storeProduct')
            ->where('user_id', $user->uuid)
            ->get();

        $before = EntitlementService::compute($user, $currentReceipts, $currentTransactions, $currentConsumables);

        [$extraReceipts, $extraTransactions, $extraConsumables] = self::buildExtras(
            $missingRows,
            $products,
            $user,
            $currentReceipts->pluck('original_transaction_id')->flip(),
            $currentTransactions->pluck('transaction_id')->flip(),
            $currentConsumables->pluck('transaction_id')->flip(),
        );

        $after = EntitlementService::compute(
            $user,
            $currentReceipts->concat($extraReceipts)->values(),
            $currentTransactions->concat($extraTransactions)->values(),
            $currentConsumables->concat($extraConsumables)->values(),
        );

        $currentEntitlementKeys = UserEntitlement::where('user_id', $user->uuid)
            ->where('source_type', EntitlementService::SOURCE_APPLE_RECEIPT)
            ->pluck('key')
            ->all();

        return [
            'missing_transactions' => $missingRows->count(),
            'before_pro' => $before['pro'],
            'before_plus' => $before['plus'],
            'before_is_pro_flag' => (bool) $user->is_pro,
            'before_is_subscribed_flag' => (bool) $user->is_subscribed,
            'after_pro' => $after['pro'],
            'after_plus' => $after['plus'],
            'before_entitlements' => $currentEntitlementKeys,
            'after_entitlements' => array_keys($after['candidates']),
            'error' => null,
        ];
    }

    /**
     * Materialise in-memory receipt/transaction/consumable rows from stored payload JSON, skipping anything that
     * would duplicate current DB state.
     *
     * @param  Collection<int, ReconciliationRow>  $missingRows
     * @param  Collection<string, StoreProduct>  $products
     * @param  Collection<string, mixed>  $existingReceiptOriginalIds
     * @param  Collection<string, mixed>  $existingTransactionIds
     * @param  Collection<string, mixed>  $existingConsumableTransactionIds
     * @return array{0: Collection<int, UserReceipt>, 1: Collection<int, UserReceiptTransaction>, 2: Collection<int, ConsumablePurchase>}
     */
    private static function buildExtras(
        Collection $missingRows,
        Collection $products,
        User $user,
        Collection $existingReceiptOriginalIds,
        Collection $existingTransactionIds,
        Collection $existingConsumableTransactionIds,
    ): array {
        $receipts = collect();
        $transactions = collect();
        $consumables = collect();
        $addedReceiptOriginalIds = [];
        $addedTransactionIds = [];

        foreach ($missingRows as $row) {
            $payload = $row->payload ?? [];
            $productId = $row->product_id ?: ($payload['productId'] ?? null);
            $txId = $row->transaction_id ?: ($payload['transactionId'] ?? null);
            $oti = $row->original_transaction_id ?: ($payload['originalTransactionId'] ?? null);

            if (!$productId || !$txId || !$oti) {
                continue;
            }

            if (isset($existingTransactionIds[$txId]) || isset($addedTransactionIds[$txId])) {
                continue;
            }

            /** @var StoreProduct|null $product */
            $product = $products->get($productId);
            if (!$product) {
                continue;
            }

            $purchaseDate = self::millisToCarbon($payload['purchaseDate'] ?? null);
            $expiresDate = self::millisToCarbon($payload['expiresDate'] ?? null);
            $revocationDate = self::millisToCarbon($payload['revocationDate'] ?? null);

            $transaction = new UserReceiptTransaction([
                'user_id' => $user->uuid,
                'transaction_id' => $txId,
                'original_transaction_id' => $oti,
                'product_id' => $product->product_id,
                'expires_at' => $expiresDate,
                'revoked_at' => $revocationDate,
                'purchased_at' => $purchaseDate,
            ]);
            $transaction->setRelation('storeProduct', $product);
            $transactions->push($transaction);
            $addedTransactionIds[$txId] = true;

            if ($product->type->is(StoreProductType::Consumable)) {
                if (isset($existingConsumableTransactionIds[$txId])) {
                    continue;
                }

                $consumable = new ConsumablePurchase([
                    'user_id' => $user->uuid,
                    'transaction_id' => $txId,
                    'product_id' => $product->product_id,
                    'purchased_at' => $purchaseDate,
                ]);
                $consumable->setRelation('storeProduct', $product);
                $consumables->push($consumable);
                continue;
            }

            if (isset($existingReceiptOriginalIds[$oti]) || isset($addedReceiptOriginalIds[$oti])) {
                continue;
            }

            $receipt = new UserReceipt([
                'user_id' => $user->uuid,
                'original_transaction_id' => $oti,
                'product_id' => $product->product_id,
            ]);
            $receipt->setRelation('storeProduct', $product);
            $receipts->push($receipt);
            $addedReceiptOriginalIds[$oti] = true;
        }

        return [$receipts, $transactions, $consumables];
    }

    private static function millisToCarbon(?int $millis): ?Carbon
    {
        return $millis !== null ? Carbon::createFromTimestampMs($millis) : null;
    }
}
