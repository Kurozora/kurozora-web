<?php

namespace App\Services;

use App\Models\ParentalGuideEntry;
use Illuminate\Support\Collection;

class ParentalGuideService
{
    /**
     * Calculate the weighted trimmed mean for parental guide entries.
     *
     * @param Collection<int, ParentalGuideEntry> $entries
     *
     * @return array
     */
    public function weightedTrimmedMean(Collection $entries): array
    {
        // Build list of [value, weight]
        $samples = $entries->map(function (ParentalGuideEntry $e) {
            $reputation = max(0, (int) $e->user?->reputation_count);
            $weight = 1 + min(log(($reputation + 1), 10), 2); // log10
            return ['value' => (int) $e->rating->value, 'weight' => (float) $weight];
        })->values();

        if ($samples->isEmpty()) {
            return ['average' => 0.0, 'votes_count' => 0];
        }

        // Sort by value asc
        $sorted = $samples->sortBy('value')->values();

        // Weighted trimming 10%
        $totalW = $sorted->sum('weight');

        if ($totalW <= 0) {
            return ['average' => 0.0, 'votes_count' => 0];
        }

        $trimFraction = 0.10;
        $trimW = $totalW * $trimFraction;

        $remaining = collect();
        $acc = 0.0;

        // Trim from the bottom
        foreach ($sorted as $row) {
            if ($acc + $row['weight'] <= $trimW) {
                $acc += $row['weight'];
                continue;
            }

            // Partial trim is not needed for simplicity
            $remaining->push($row);
        }

        // Trim from the top
        $remainingRev = $remaining->reverse()->values();
        $accTop = 0.0;
        $final = collect();

        foreach ($remainingRev as $row) {
            if ($accTop + $row['weight'] <= $trimW) {
                $accTop += $row['weight'];
                continue;
            }
            $final->push($row);
        }

        $final = $final->reverse()->values();
        $sumW = max(1e-9, $final->sum('weight'));
        $sumVW = $final->sum(fn($r) => $r['value'] * $r['weight']);
        $average = $sumVW / $sumW;

        return [
            'average' => max(0.0, min(3.0, $average)),
            'votes_count' => $entries->count(), // Raw count
        ];
    }

    public function roundToLabel(float $averageNumeric): int
    {
        // 0..3 — conservative ties (x.5 rounds down)
        $floor = floor($averageNumeric);
        $fraction = $averageNumeric - $floor;

        if ($fraction > 0.5) {
            return (int) min(3, $floor + 1);
        }

        return (int) $floor;
    }
}
