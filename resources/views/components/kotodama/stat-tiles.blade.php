@props(['stats', 'winRate'])

<div {{ $attributes->merge(['class' => 'flex flex-wrap -mr-2']) }}>
    <div class="w-1/2 pr-2 pb-2 md:w-1/4 md:pb-0">
        <div class="flex flex-col items-center justify-center rounded border border-primary bg-secondary pl-3 pr-3 pt-3 pb-3 text-center">
            <span class="text-2xl font-bold text-primary">{{ $stats?->games_played ?? 0 }}</span>
            <span class="text-xs text-secondary">{{ __('Games played') }}</span>
        </div>
    </div>

    <div class="w-1/2 pr-2 pb-2 md:w-1/4 md:pb-0">
        <div class="flex flex-col items-center justify-center rounded border border-primary bg-secondary pl-3 pr-3 pt-3 pb-3 text-center">
            <span class="text-2xl font-bold text-primary">{{ $winRate }}%</span>
            <span class="text-xs text-secondary">{{ __('Win rate') }}</span>
        </div>
    </div>

    <div class="w-1/2 pr-2 md:w-1/4">
        <div class="flex flex-col items-center justify-center rounded border border-primary bg-secondary pl-3 pr-3 pt-3 pb-3 text-center">
            <span class="text-2xl font-bold text-primary">{{ $stats?->current_streak ?? 0 }}</span>
            <span class="text-xs text-secondary">{{ __('Current streak') }}</span>
        </div>
    </div>

    <div class="w-1/2 pr-2 md:w-1/4">
        <div class="flex flex-col items-center justify-center rounded border border-primary bg-secondary pl-3 pr-3 pt-3 pb-3 text-center">
            <span class="text-2xl font-bold text-primary">{{ $stats?->max_streak ?? 0 }}</span>
            <span class="text-xs text-secondary">{{ __('Max streak') }}</span>
        </div>
    </div>
</div>
