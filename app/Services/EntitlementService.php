<?php

namespace App\Services;

use App\Models\ConsumablePurchase;
use App\Models\User;
use App\Models\UserEntitlement;
use App\Models\UserReceipt;
use App\Models\UserReceiptTransaction;
use AppStoreServerLibrary\Models\OfferDiscountType;
use Illuminate\Support\Collection;

final class EntitlementService
{
    public const string SOURCE_APPLE_RECEIPT = 'apple_receipt';

    /**
     * Recompute the user's Apple-receipt-backed entitlements.
     *
     * @return array{pro: bool, plus: bool}
     */
    public static function recompute(User $user): array
    {
        $computed = self::compute($user);

        self::persist($user, $computed);

        return [
            'pro' => $computed['pro'],
            'plus' => $computed['plus'],
        ];
    }

    /**
     * Compute the user's Apple-receipt-backed entitlement state.
     *
     * @param  Collection<int, UserReceipt>|null  $userReceipts
     * @param  Collection<int, UserReceiptTransaction>|null  $userReceiptTransactions
     * @param  Collection<int, ConsumablePurchase>|null  $consumablePurchases
     * @return array{candidates: array<string, array{expires_at: ?\Illuminate\Support\Carbon, source_id: string}>, pro: bool, plus: bool, receipt_states: array<string, array{expires_at: ?\Illuminate\Support\Carbon, is_subscribed: bool}>}
     */
    public static function compute(
        User $user,
        ?Collection $userReceipts = null,
        ?Collection $userReceiptTransactions = null,
        ?Collection $consumablePurchases = null,
    ): array {
        $userReceipts ??= UserReceipt::with('storeProduct')
            ->where('user_id', $user->uuid)
            ->get();

        $userReceiptTransactions ??= UserReceiptTransaction::where('user_id', $user->uuid)->get();

        $consumablePurchases ??= ConsumablePurchase::with('storeProduct')
            ->where('user_id', $user->uuid)
            ->get();

        $revokedTransactionIds = $userReceiptTransactions
            ->whereNotNull('revoked_at')
            ->pluck('transaction_id')
            ->flip();

        $consumablePurchases = $consumablePurchases->filter(
            fn (ConsumablePurchase $p) => $p->revoked_at === null
                && !isset($revokedTransactionIds[$p->transaction_id])
        );

        $transactionsByOriginal = $userReceiptTransactions->groupBy('original_transaction_id');

        $candidates = [];
        $receiptStates = [];

        foreach ($userReceipts as $userReceipt) {
            $receiptTransactions = $transactionsByOriginal->get($userReceipt->original_transaction_id) ?? collect();

            $latestTransaction = $receiptTransactions
                ->sortByDesc(fn (UserReceiptTransaction $tx) => $tx->expires_at?->getTimestamp() ?? PHP_INT_MIN)
                ->first();

            if (!$latestTransaction) {
                continue;
            }

            $inGracePeriod = $userReceipt->grace_period_expires_date !== null
                && $userReceipt->grace_period_expires_date->isFuture();

            $active = !$latestTransaction->revoked_at && (
                $latestTransaction->expires_at === null
                || $latestTransaction->expires_at->isFuture()
                || $inGracePeriod
            );

            $receiptStates[$userReceipt->original_transaction_id] = [
                'expires_at' => $latestTransaction->expires_at,
                'is_subscribed' => $active,
            ];

            $storeProduct = $userReceipt->storeProduct;
            if (!$storeProduct) {
                continue;
            }

            $hasPaidTransaction = $receiptTransactions->contains(
                fn (UserReceiptTransaction $tx) => !$tx->is_trial_period
                    && $tx->offer_discount_type !== OfferDiscountType::FREE_TRIAL->value
                    && !$tx->revoked_at
            );

            foreach ($storeProduct->entitlements ?? [] as $key) {
                if ($key === 'pro') {
                    if (!$hasPaidTransaction && !$active) {
                        continue;
                    }
                    $candidateExpiresAt = $hasPaidTransaction ? null : $latestTransaction->expires_at;
                } else {
                    if (!$active) {
                        continue;
                    }
                    $candidateExpiresAt = $latestTransaction->expires_at;
                }

                if (!isset($candidates[$key])) {
                    $candidates[$key] = [
                        'expires_at' => $candidateExpiresAt,
                        'source_id' => $userReceipt->original_transaction_id,
                    ];
                    continue;
                }

                $existing = $candidates[$key]['expires_at'];

                if ($existing === null) {
                    continue;
                }

                if ($candidateExpiresAt === null || $candidateExpiresAt->gt($existing)) {
                    $candidates[$key] = [
                        'expires_at' => $candidateExpiresAt,
                        'source_id' => $userReceipt->original_transaction_id,
                    ];
                }
            }
        }

        foreach ($consumablePurchases as $purchase) {
            $storeProduct = $purchase->storeProduct;
            if (!$storeProduct) {
                continue;
            }

            foreach ($storeProduct->entitlements ?? [] as $key) {
                $candidates[$key] = [
                    'expires_at' => null,
                    'source_id' => $purchase->transaction_id,
                ];
            }
        }

        $keys = array_keys($candidates);

        return [
            'candidates' => $candidates,
            'pro' => in_array('pro', $keys, true),
            'plus' => in_array('plus', $keys, true),
            'receipt_states' => $receiptStates,
        ];
    }

    /**
     * Persist the result of compute().
     *
     * @param  array{candidates: array<string, array{expires_at: ?\Illuminate\Support\Carbon, source_id: string}>, pro: bool, plus: bool, receipt_states: array<string, array{expires_at: ?\Illuminate\Support\Carbon, is_subscribed: bool}>}  $computed
     */
    public static function persist(User $user, array $computed): void
    {
        foreach ($computed['receipt_states'] as $originalTransactionId => $state) {
            UserReceipt::where('user_id', $user->uuid)
                ->where('original_transaction_id', $originalTransactionId)
                ->update([
                    'expires_at' => $state['expires_at'],
                    'is_subscribed' => $state['is_subscribed'],
                ]);
        }

        $finalKeys = [];

        foreach ($computed['candidates'] as $key => $data) {
            // Preserve granted_at across recomputes; only stamp it on the initial grant.
            $entitlement = UserEntitlement::firstOrNew([
                'user_id' => $user->uuid,
                'key' => $key,
            ]);

            $entitlement->source_type = self::SOURCE_APPLE_RECEIPT;
            $entitlement->source_id = $data['source_id'];
            $entitlement->expires_at = $data['expires_at'];

            if (!$entitlement->exists) {
                $entitlement->granted_at = now();
            }

            $entitlement->save();

            $finalKeys[] = $key;
        }

        UserEntitlement::where('user_id', $user->uuid)
            ->where('source_type', self::SOURCE_APPLE_RECEIPT)
            ->whereNotIn('key', $finalKeys)
            ->delete();

        $user->update([
            'is_pro' => $computed['pro'],
            'is_subscribed' => $computed['plus'],
            'subscribed_at' => $computed['plus']
                ? UserEntitlement::where('user_id', $user->uuid)
                    ->where('key', 'plus')
                    ->value('granted_at')
                : null,
        ]);
    }
}

