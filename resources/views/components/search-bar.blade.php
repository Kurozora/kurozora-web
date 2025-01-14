@props(['searchModel' => 'search'])

<div>
    <div class="gap-2 items-center mt-4 mb-4 space-y-2 sm:flex sm:space-y-0">
        <div class="flex flex-1 gap-2 items-center">
            <x-input id="search" class="w-full" type="text" placeholder="{{ __('I’m searching for…') }}" wire:model.live.debounce.500ms="{{ $searchModel }}" />

            <livewire:components.search-hint-button />
        </div>

        <div class="flex flex-1 items-center justify-end space-x-1">
            <x-spinner />

            {{-- Order --}}
            @if (!empty($this->order))
                <x-dropdown align="right" width="48" max-height="350px">
                    <x-slot:trigger>
                        <x-square-button>
                            @svg('arrow_up_arrow_down_circle', 'fill-current', ['aria-labelledby' => 'order', 'width' => '28'])
                        </x-square-button>
                    </x-slot:trigger>

                    <x-slot:content>
                        @if (collect($this->order)->contains('selected', '!=', null))
                            {{-- Reset Order --}}
                            <button class="block w-full pl-4 pr-4 pt-2 pb-2 bg-secondary text-xs text-center text-secondary font-semibold hover:bg-tertiary focus:bg-secondary" wire:click="resetOrder">
                                {{ __('Reset Order') }}
                            </button>
                        @endif

                        @foreach ($this->order as $key => $order)
                            <div class="block pl-4 pr-4 pt-2 pb-2 bg-secondary text-xs text-secondary font-semibold">
                                {{ $order['title'] }}
                            </div>

                            <div class="block pl-4 pr-4 pt-2 pb-2">
                                <x-select id="{{ $key }}" wire:model.live="order.{{ $key }}.selected">
                                    @foreach ($order['options'] as $optionKey => $option)
                                        <option value="{{ $option }}">{{ __($optionKey) }}</option>
                                    @endforeach
                                </x-select>
                            </div>
                        @endforeach
                    </x-slot:content>
                </x-dropdown>
            @endif

            {{-- Filter --}}
            @if (!empty($this->filter))
                <x-dropdown align="right" width="48" max-height="350px">
                    <x-slot:trigger>
                        <x-square-button>
                            @svg('line_3_horizontal_decrease_circle', 'fill-current', ['aria-labelledby' => 'filter', 'width' => '28'])
                        </x-square-button>
                    </x-slot:trigger>

                    <x-slot:content>
                        @if (collect($this->filter)->contains('selected', '!=', null))
                            {{-- Reset Order --}}
                            <button class="block w-full pl-4 pr-4 pt-2 pb-2 bg-secondary text-xs text-center text-secondary font-semibold hover:bg-tertiary focus:bg-secondary" wire:click="resetFilter">
                                {{ __('Reset Filters') }}
                            </button>
                        @endif

                        @foreach ($this->filter as $key => $filter)
                            {{-- Per Page --}}
                            <div class="block pl-4 pr-4 pt-2 pb-2 bg-secondary text-xs text-secondary font-semibold">
                                {{ $filter['title'] }}
                            </div>

                            <div class="block pl-4 pr-4 pt-2 pb-2">
                                @switch($filter['type'])
                                    @case('string')
                                        <x-input id="{{ $key }}" class="w-full" type="text" wire:model.live="filter.{{ $key }}.selected" />
                                        @break
                                    @case('number')
                                        <x-input id="{{ $key }}" class="w-full" type="number" wire:model.live="filter.{{ $key }}.selected" />
                                        @break
                                    @case('double')
                                        <x-input id="{{ $key }}" class="w-full" type="number" step="0.01" wire:model.live="filter.{{ $key }}.selected" />
                                        @break
                                    @case('date')
                                        <x-input id="{{ $key }}" class="w-full" type="date" wire:model.live="filter.{{ $key }}.selected" />
                                        @break
                                    @case('duration')
                                        <x-input id="{{ $key }}" class="w-full" type="number" step="1" wire:model.live="filter.{{ $key }}.selected" />
                                        @break
                                    @case('time')
                                        <x-input id="{{ $key }}" class="w-full" type="time" wire:model.live="filter.{{ $key }}.selected" />
                                        @break
                                    @case('day')
                                        <x-select id="{{ $key }}" wire:model.live="filter.{{ $key }}.selected">
                                            <option value="">{{ __('Default') }}</option>
                                            @foreach (range(1, 31) as $day)
                                                <option value="{{ strlen($day) == 1 ? '0' . $day : $day }}">
                                                    {{ strlen($day) == 1 ? '0' . $day : $day }}
                                                </option>
                                            @endforeach
                                        </x-select>
                                        @break
                                    @case('month')
                                        <x-select id="{{ $key }}" wire:model.live="filter.{{ $key }}.selected">
                                            <option value="">{{ __('Default') }}</option>
                                            @foreach (range(1, 12) as $month)
                                                <option value="{{ $month }}">
                                                    {{ date('F', strtotime('2018-' . $month)) }}
                                                </option>
                                            @endforeach
                                        </x-select>
                                        @break
                                    @case('multiselect')
                                        <x-select id="{{ $key }}" wire:model.live="filter.{{ $key }}.selected" multiple>
                                            @foreach ($filter['options'] as $optionKey => $option)
                                                <option value="{{ $optionKey }}">{{ __($option) }}</option>
                                            @endforeach
                                        </x-select>
                                        @break
                                    @case('select')
                                        <x-select id="{{ $key }}" wire:model.live="filter.{{ $key }}.selected">
                                            <option value="">{{ __('Default') }}</option>
                                            @foreach ($filter['options'] as $optionKey => $option)
                                                <option value="{{ $optionKey }}">{{ __($option) }}</option>
                                            @endforeach
                                        </x-select>
                                        @break
                                    @case('bool')
                                        <x-select id="{{ $key }}" wire:model.live="filter.{{ $key }}.selected">
                                            <option value="">{{ __('Default') }}</option>
                                            @foreach ($filter['options'] as $optionKey => $option)
                                                <option value="{{ (int) ($optionKey == 0) }}">{{ __($option) }}</option>
                                            @endforeach
                                        </x-select>
                                        @break
                                @endswitch
                            </div>
                        @endforeach

                        {{-- Per Page --}}
                        <div class="block pl-4 pr-4 pt-2 pb-2 bg-secondary text-xs text-secondary font-semibold">
                            {{ __('Per Page') }}
                        </div>

                        <div class="block pl-4 pr-4 pt-2 pb-2">
                            <x-select id="perPage" wire:model.live="perPage">
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </x-select>
                        </div>
                    </x-slot:content>
                </x-dropdown>
            @endif

            @if (!empty($rightBarButtonItems))
                {{ $rightBarButtonItems }}
            @endif

            {{-- Lettered Index --}}
            @if (!empty($this->letteredIndex))
                <x-select width="" wire:model.live="letter">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($this->letteredIndex as $optionKey => $option)
                        <option value="{{ $option }}">{{ __($optionKey) }}</option>
                    @endforeach
                </x-select>
            @endif
        </div>
    </div>

    <x-hr class="mt-4 mb-4" />

    <div
        x-data="{
            type: @entangle('type').live
        }"
        class="flex gap-2 whitespace-nowrap overflow-x-scroll no-scrollbar"
    >
        @foreach ($this->searchTypes as $value)
            @php($type = str($value)->slug())
            <template x-if="type === '{{ $type }}'">
                <x-button>{{ __($value) }}</x-button>
            </template>

            <template x-if="type !== '{{ $type }}'">
                <x-outlined-button
                    x-on:click="type = '{{ $type }}'"
                >{{ __($value) }}</x-outlined-button>
            </template>
        @endforeach
    </div>
</div>
