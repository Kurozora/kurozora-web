<?php

namespace App\Nova\Metrics;

use App\Console\Commands\Refreshers\AppStore\RefreshAppleRootCerts;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\MetricTableRow;
use Laravel\Nova\Metrics\Table;

class AppleRootCertsStatus extends Table
{
    /**
     * How many hours since the last check before the status is considered stale.
     */
    private const int STALE_AFTER_HOURS = 36;

    /**
     * Pinned files surfaced on the dashboard.
     *
     * @var string[]
     */
    private const array PINNED_FILES = [
        'AppleRootCA-G2.cer',
        'AppleRootCA-G3.cer',
    ];

    /**
     * Calculate the value of the metric.
     *
     * @param NovaRequest $request
     *
     * @return array<int, MetricTableRow>
     */
    public function calculate(NovaRequest $request): array
    {
        $status = Cache::get(RefreshAppleRootCerts::STATUS_CACHE_KEY);
        $checkedAt = is_array($status) && isset($status['checked_at'])
            ? Carbon::parse($status['checked_at'])
            : null;

        return [
            $this->headerRow($status, $checkedAt),
            ...array_map(
                fn(string $filename) => $this->fileRow($filename, $status),
                self::PINNED_FILES,
            ),
        ];
    }

    /**
     * Summary row showing overall status + Re-check action.
     *
     * @param null|array  $status
     * @param null|Carbon $checkedAt
     *
     * @return MetricTableRow
     */
    private function headerRow(?array $status, ?Carbon $checkedAt): MetricTableRow
    {
        [$icon, $iconClass, $title, $subtitle] = match (true) {
            !is_array($status) => ['shield-exclamation', 'text-gray-400', 'No check yet', 'Trigger the first run with Re-check.'],
            $checkedAt === null || $checkedAt->diffInHours() > self::STALE_AFTER_HOURS => ['exclamation-triangle', 'text-yellow-500', 'Check is stale', $this->checkedAtSubtitle($checkedAt)],
            !($status['ok'] ?? false) => ['shield-exclamation', 'text-red-500', 'Drift detected', $this->checkedAtSubtitle($checkedAt)],
            default => ['shield-check', 'text-green-500', 'All pins match Apple', $this->checkedAtSubtitle($checkedAt)],
        };

        return MetricTableRow::make()
            ->icon($icon)
            ->iconClass($iconClass)
            ->title($title)
            ->subtitle($subtitle)
            ->actions(fn() => [
                [
                    'name' => 'Re-check now',
                    'path' => route('admin.apple-root-certs.refresh'),
                    'method' => 'POST',
                    'headers' => [
                        'X-CSRF-TOKEN' => csrf_token(),
                        'Accept' => 'application/json',
                    ],
                ],
            ]);
    }

    /**
     * Per-file row showing the pin state for one certificate.
     *
     * @param string     $filename
     * @param null|array $status
     *
     * @return MetricTableRow
     */
    private function fileRow(string $filename, ?array $status): MetricTableRow
    {
        $file = $status['files'][$filename] ?? null;

        [$icon, $iconClass, $subtitle] = match (true) {
            !is_array($file) => ['question-mark-circle', 'text-gray-400', 'No data yet'],
            !($file['disk_ok'] ?? false) => ['x-circle', 'text-red-500', 'Committed cert does not match pin'],
            !($file['remote_reachable'] ?? false) => ['signal-slash', 'text-yellow-500', 'apple.com unreachable — pin intact on disk'],
            !($file['remote_ok'] ?? false) => ['x-circle', 'text-red-500', 'apple.com is serving a different cert'],
            default => ['check-circle', 'text-green-500', 'Matches pin on disk and apple.com'],
        };

        return MetricTableRow::make()
            ->icon($icon)
            ->iconClass($iconClass)
            ->title($filename)
            ->subtitle($subtitle);
    }

    /**
     * Generate a subtitle describing the last check time.
     *
     * @param null|Carbon $checkedAt
     *
     * @return string
     */
    private function checkedAtSubtitle(?Carbon $checkedAt): string
    {
        if ($checkedAt === null) {
            return 'Last check time unknown.';
        }

        return sprintf('Last checked %s (%s).', $checkedAt->toIso8601String(), $checkedAt->diffForHumans());
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'apple-root-certs-status';
    }

    /**
     * Get the name of the metric.
     *
     * @return string
     */
    public function name(): string
    {
        return 'Apple Root Certs';
    }
}
