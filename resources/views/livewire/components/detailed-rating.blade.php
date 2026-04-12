<div class="relative">
    {{-- Trigger button --}}
    <button
            type="button"
            wire:click="openModal"
            @class([
                'flex items-center gap-2 px-4 py-2 rounded-lg transition-all duration-200',
                'bg-secondary hover:bg-tertiary cursor-pointer' => !$disabled,
                'bg-secondary cursor-not-allowed opacity-75'    => $disabled,
            ])
            @disabled($disabled)
    >
        @if ($rating > 0)
            <span class="text-lg font-semibold text-tint">{{ number_format($rating, 1) }}</span>
            <span class="text-sm text-secondary">/10</span>
        @else
            <span class="text-sm text-secondary">{{ __('Rate in Detail') }}</span>
        @endif
    </button>

    @if ($showModal)
        <div
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
                wire:click.self="closeModal"
                x-data="{
                scores: @js(
                    collect($this->categories)->mapWithKeys(fn ($cat) => [
                        (string) $cat->rating_category_id => (float) ($existingScores[(string) $cat->rating_category_id] ?? 5.0)
                    ])->all()
                ),
                categories: @js(
                    collect($this->categories)->map(fn ($cat) => [
                        'id'     => (string) $cat->rating_category_id,
                        'name'   => $cat->ratingCategory?->name ?? __('Category'),
                        'desc'   => $cat->ratingCategory?->description ?? '',
                        'weight' => (float) ($cat->ratingCategory?->weight ?? 1.0),
                    ])->values()->all()
                ),
                description: @js($existingDescription),
                get overall() {
                    let ws = 0, tw = 0;
                    this.categories.forEach(c => {
                        ws += (parseFloat(this.scores[c.id]) || 5) * c.weight;
                        tw += c.weight;
                    });
                    return tw > 0 ? Math.round(ws / tw * 10) / 10 : 5;
                }
            }"
        >
            <div class="w-full max-w-3xl bg-primary rounded-2xl border border-separator shadow-2xl flex flex-col max-h-[88vh] overflow-hidden">

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-separator flex-shrink-0">
                    <div>
                        <h3 class="text-sm font-semibold text-primary leading-tight">{{ __('Detailed Rating') }}</h3>
                        <p class="text-xs text-secondary mt-0.5">{{ __('Score each category and share your thoughts') }}</p>
                    </div>
                    <button
                            type="button"
                            wire:click="closeModal"
                            class="w-7 h-7 flex items-center justify-center rounded-full bg-secondary hover:bg-tertiary transition-colors flex-shrink-0"
                    >
                        @svg('xmark', 'w-4 h-4 fill-current text-secondary')
                    </button>
                </div>

                {{-- Body: split layout --}}
                <div class="flex flex-1 min-h-0 divide-x divide-separator">

                    {{-- Left: category sliders --}}
                    <div class="w-[55%] flex-shrink-0 overflow-y-auto px-5 py-4 flex flex-col gap-2.5">
                        <p class="text-[10px] font-medium text-secondary uppercase tracking-wider mb-1">{{ __('Categories') }}</p>

                        <template x-if="categories.length > 0">
                            <div class="flex flex-col gap-2.5">
                                <template x-for="cat in categories" :key="cat.id">
                                    <div class="flex flex-col gap-2 p-3 bg-secondary rounded-xl border border-separator hover:border-opacity-60 transition-colors">

                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs font-medium text-primary leading-snug" x-text="cat.name"></p>
                                                <p class="text-[10px] text-secondary mt-0.5 leading-snug" x-text="cat.desc" x-show="cat.desc"></p>
                                            </div>
                                            <div class="flex items-baseline gap-px flex-shrink-0 pt-px">
                                                <span class="text-sm font-medium text-primary tabular-nums" x-text="parseFloat(scores[cat.id] || 5).toFixed(1)"></span>
                                                <span class="text-[10px] text-secondary">/10</span>
                                            </div>
                                        </div>

                                        <input
                                                type="range"
                                                min="0"
                                                max="10"
                                                step="0.5"
                                                class="w-full accent-tint cursor-pointer"
                                                x-bind:value="scores[cat.id] ?? 5"
                                                x-on:input="scores[cat.id] = parseFloat($event.target.value)"
                                        />

                                        <div class="flex justify-between text-[9px] text-secondary opacity-40 select-none px-px -mt-1">
                                            <span>0</span>
                                            <span>5</span>
                                            <span>10</span>
                                        </div>

                                        <div class="h-px bg-tertiary rounded-full overflow-hidden">
                                            <div
                                                    class="h-full bg-tint rounded-full transition-all duration-150"
                                                    x-bind:style="'width:' + ((scores[cat.id] ?? 5) * 10) + '%'"
                                            ></div>
                                        </div>

                                    </div>
                                </template>
                            </div>
                        </template>

                        <template x-if="categories.length === 0">
                            <div class="flex items-center justify-center py-12">
                                <span class="text-xs text-secondary">{{ __('Loading…') }}</span>
                            </div>
                        </template>
                    </div>

                    {{-- Right: review --}}
                    <div class="flex-1 flex flex-col px-5 py-4 gap-3 overflow-hidden">
                        <p class="text-[10px] font-medium text-secondary uppercase tracking-wider flex-shrink-0">{{ __('Your Review') }}</p>

                        <textarea
                                x-model="description"
                                maxlength="2000"
                                placeholder="{{ __('Share your thoughts in detail — what worked, what didn\'t, how it made you feel...') }}"
                                class="flex-1 w-full bg-secondary border border-separator rounded-xl px-4 py-3 text-sm text-primary placeholder-secondary resize-none outline-none focus:border-opacity-60 leading-relaxed transition-colors min-h-0"
                        ></textarea>

                        <div class="flex items-center justify-end flex-shrink-0">
                            <span class="text-[10px] text-secondary opacity-60" x-text="description.length + ' / 2000'"></span>
                        </div>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 border-t border-separator bg-secondary/20 flex-shrink-0 space-y-3">

                    <div class="flex items-center justify-between">
                        <span class="text-xs text-secondary">{{ __('Overall') }}</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-2xl font-bold text-primary tabular-nums" x-text="overall.toFixed(1)"></span>
                            <span class="text-xs text-secondary">/10</span>
                        </div>
                    </div>

                    <div class="w-full h-0.5 bg-tertiary rounded-full overflow-hidden">
                        <div
                                class="h-full bg-tint rounded-full transition-all duration-200"
                                x-bind:style="'width:' + (overall * 10) + '%'"
                        ></div>
                    </div>

                    <div class="flex gap-2 pt-1">
                        @if ($rating > 0)
                            <button
                                    type="button"
                                    wire:click="removeRating"
                                    wire:confirm="{{ __('Remove your rating?') }}"
                                    class="shrink-0 px-4 py-2 text-xs font-medium rounded-lg text-red-500 bg-red-500/10 hover:bg-red-500/20 transition-colors"
                            >
                                {{ __('Remove') }}
                            </button>
                        @endif
                        <button
                                type="button"
                                x-on:click="$wire.rate(scores, description)"
                                class="flex-1 py-2 text-xs font-semibold rounded-lg text-white bg-tint hover:opacity-90 active:opacity-80 transition-opacity"
                        >
                            {{ __('Save Rating') }}
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif
</div>