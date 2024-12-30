<?php

namespace App\Nova\Metrics;

use App\Models\User;
use App\Models\UserReceipt;
use DateInterval;
use DateTimeInterface;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class UsersPerPlan extends Partition
{
    /**
     * Calculate the value of the metric.
     *
     * @param NovaRequest $request
     *
     * @return mixed
     */
    public function calculate(NovaRequest $request): mixed
    {
        $counts = User::leftJoin(UserReceipt::TABLE_NAME, User::TABLE_NAME . '.uuid', '=', UserReceipt::TABLE_NAME . '.user_id')
            ->select([User::TABLE_NAME . '.uuid', UserReceipt::TABLE_NAME . '.product_id'])
            ->get()
            ->groupBy('product_id')
            ->map(fn($group) => $group->count())
            ->mapWithKeys(function ($count, $key) {
                return [$this->formatKeyName($key) => $count];
            })
            ->toArray();


        return $this->result($counts);
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return DateTimeInterface|DateInterval|float|int|null
     */
    public function cacheFor(): DateInterval|float|DateTimeInterface|int|null
    {
        return 60 * 24;
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'users-per-plan';
    }

    /**
     * Returns a human friendly key name.
     *
     * @param string $key
     *
     * @return string
     */
    private function formatKeyName(string $key): string
    {
        $key = explode('.', $key);
        $key = end($key);

        return match ($key) {
            '' => 'None',
            'kPlus1Month' => 'Kurozora+ 1 Mo.',
            'kPlus6Months' => 'Kurozora+ 6 Mo.',
            'kPlus12Months' => 'Kurozora+ 12 Mo.',
            'wolfTip' => '🐺 Wolf Tip',
            'tigerTip' => '🐯 Tiger Tip',
            'demonTip' => '👺 Demon Tip',
            'dragonTip' => '🐲 Dragon Tip',
            'godTip' => '🙏 God Tip',
            'eternalTip' => '♾️ Eternal Tip',
            'kurozoraOne' => 'Kurozora One',
            default => $key
        };
    }
}
