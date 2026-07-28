@props(['stats', 'winRate', 'recentResults'])

<section class="pt-10 pl-4 pr-4 xl:safe-area-inset-scroll">
    <div class="flex flex-col gap-3 rounded-xl border border-primary bg-secondary p-4">
        <dl class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="flex flex-col">
                <dt class="text-xs text-secondary uppercase tracking-wide">{{ __('Streak') }}</dt>
                <dd class="text-xl font-bold text-primary">{{ $stats?->current_streak ?? 0 }}</dd>
            </div>

            <div class="flex flex-col">
                <dt class="text-xs text-secondary uppercase tracking-wide">{{ __('Best') }}</dt>
                <dd class="text-xl font-bold text-primary">{{ $stats?->max_streak ?? 0 }}</dd>
            </div>

            <div class="flex flex-col">
                <dt class="text-xs text-secondary uppercase tracking-wide">{{ __('Played') }}</dt>
                <dd class="text-xl font-bold text-primary">{{ $stats?->games_played ?? 0 }}</dd>
            </div>

            <div class="flex flex-col">
                <dt class="text-xs text-secondary uppercase tracking-wide">{{ __('Won') }}</dt>
                <dd class="text-xl font-bold text-primary">{{ $winRate }}%</dd>
            </div>
        </dl>

        @if($recentResults->isNotEmpty())
            <div class="flex items-center gap-2">
                <span class="text-xs text-secondary uppercase tracking-wide">{{ __('Recent') }}</span>

                <ol class="flex items-center gap-1">
                    @foreach($recentResults as $result)
                        <li
                            @class(['h-2 w-2 rounded-full', 'bg-tint' => $result->won, 'bg-tertiary' => !$result->won])
                            title="{{ __('Kotodama · Daily #:number', ['number' => $result->number]) }}"
                        ></li>
                    @endforeach
                </ol>
            </div>
        @endif
    </div>
</section>
