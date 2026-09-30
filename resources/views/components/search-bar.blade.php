@props(['criteria', 'action', 'hidden' => []])

<form id="search-bar" method="get" action="{{ $action }}" data-search-bar>
    @foreach ($hidden as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endforeach

    <div class="gap-2 items-center mt-4 mb-4 pl-4 pr-4 space-y-2 sm:flex sm:space-y-0">
        <div class="flex flex-1 gap-2 items-center">
            <x-input class="w-full" type="search" name="q" value="{{ $criteria->search }}" placeholder="{{ __('I’m searching for…') }}" />

            <x-search-hint-button />
        </div>

        <div class="flex flex-1 items-center justify-end space-x-1">
            <x-spinner :wire-loading-enabled="false" class="hidden" data-loading />

            {{-- Order --}}
            @if (!empty($criteria->order))
                <x-dropdown id="order-options" align="right" width="48" max-height="350px">
                    <x-slot:trigger>
                        <x-square-button>
                            @if ($criteria->isOrdering())
                                @svg('arrow_up_arrow_down_circle_fill', 'fill-current', ['aria-labelledby' => 'order', 'width' => '28'])
                            @else
                                @svg('arrow_up_arrow_down_circle', 'fill-current', ['aria-labelledby' => 'order', 'width' => '28'])
                            @endif
                        </x-square-button>
                    </x-slot:trigger>

                    <x-slot:content>
                        @if ($criteria->isOrdering())
                            {{-- Reset Order --}}
                            <button class="block w-full pl-4 pr-4 pt-2 pb-2 bg-secondary text-xs text-center text-secondary font-semibold hover:bg-tertiary focus:bg-secondary" type="button" data-search-reset="order">
                                {{ __('Reset Order') }}
                            </button>
                        @endif

                        @foreach ($criteria->order as $key => $order)
                            <div class="block pl-4 pr-4 pt-2 pb-2 bg-secondary text-xs text-secondary font-semibold">
                                {{ $order['title'] }}
                            </div>

                            <div class="block pl-4 pr-4 pt-2 pb-2">
                                <x-select id="order-{{ $key }}" name="order[{{ $key }}]">
                                    @foreach ($order['options'] as $optionKey => $option)
                                        <option value="{{ $option }}" @selected($order['selected'] === $option)>{{ __($optionKey) }}</option>
                                    @endforeach
                                </x-select>
                            </div>
                        @endforeach
                    </x-slot:content>
                </x-dropdown>
            @endif

            {{-- Filter --}}
            @if (!empty($criteria->filter))
                <x-dropdown id="filter-options" align="right" width="48" max-height="350px">
                    <x-slot:trigger>
                        <x-square-button>
                            @if ($criteria->isFiltering())
                                @svg('line_3_horizontal_decrease_circle_fill', 'fill-current', ['aria-labelledby' => 'filter', 'width' => '28'])
                            @else
                                @svg('line_3_horizontal_decrease_circle', 'fill-current', ['aria-labelledby' => 'filter', 'width' => '28'])
                            @endif
                        </x-square-button>
                    </x-slot:trigger>

                    <x-slot:content>
                        @if ($criteria->isFiltering())
                            {{-- Reset Filter --}}
                            <button class="block w-full pl-4 pr-4 pt-2 pb-2 bg-secondary text-xs text-center text-secondary font-semibold hover:bg-tertiary focus:bg-secondary" type="button" data-search-reset="filter">
                                {{ __('Reset Filters') }}
                            </button>
                        @endif

                        @foreach ($criteria->filter as $key => $filter)
                            @php
                                $name = 'filter[' . $key . ']';
                                $selected = $filter['selected'];
                            @endphp

                            <div class="block pl-4 pr-4 pt-2 pb-2 bg-secondary text-xs text-secondary font-semibold">
                                {{ $filter['title'] }}
                            </div>

                            <div class="block pl-4 pr-4 pt-2 pb-2">
                                @switch($filter['type'])
                                    @case('string')
                                        <x-input id="filter-{{ $key }}" class="w-full" type="text" name="{{ $name }}" value="{{ $selected }}" />
                                        @break
                                    @case('number')
                                        <x-input id="filter-{{ $key }}" class="w-full" type="number" name="{{ $name }}" value="{{ $selected }}" />
                                        @break
                                    @case('double')
                                        <x-input id="filter-{{ $key }}" class="w-full" type="number" step="0.01" name="{{ $name }}" value="{{ $selected }}" />
                                        @break
                                    @case('date')
                                        <x-input id="filter-{{ $key }}" class="w-full" type="date" name="{{ $name }}" value="{{ $selected }}" />
                                        @break
                                    @case('duration')
                                        <x-input id="filter-{{ $key }}" class="w-full" type="number" step="1" name="{{ $name }}" value="{{ $selected }}" />
                                        @break
                                    @case('time')
                                        <x-input id="filter-{{ $key }}" class="w-full" type="time" name="{{ $name }}" value="{{ $selected }}" />
                                        @break
                                    @case('day')
                                        <x-select id="filter-{{ $key }}" name="{{ $name }}">
                                            <option value="">{{ __('Default') }}</option>
                                            @foreach (range(1, 31) as $day)
                                                @php
                                                    $day = str_pad($day, 2, '0', STR_PAD_LEFT);
                                                @endphp

                                                <option value="{{ $day }}" @selected($selected === $day)>{{ $day }}</option>
                                            @endforeach
                                        </x-select>
                                        @break
                                    @case('month')
                                        <x-select id="filter-{{ $key }}" name="{{ $name }}">
                                            <option value="">{{ __('Default') }}</option>
                                            @foreach (range(1, 12) as $month)
                                                <option value="{{ $month }}" @selected($selected === (string) $month)>{{ date('F', strtotime('2018-' . $month)) }}</option>
                                            @endforeach
                                        </x-select>
                                        @break
                                    @case('multiselect')
                                        <x-select id="filter-{{ $key }}" name="{{ $name }}[]" multiple>
                                            @foreach ($filter['options'] as $optionKey => $option)
                                                <option value="{{ $optionKey }}" @selected(in_array((string) $optionKey, $selected ?? [], true))>{{ __($option) }}</option>
                                            @endforeach
                                        </x-select>
                                        @break
                                    @case('select')
                                        <x-select id="filter-{{ $key }}" name="{{ $name }}">
                                            <option value="">{{ __('Default') }}</option>
                                            @foreach ($filter['options'] as $optionKey => $option)
                                                <option value="{{ $optionKey }}" @selected($selected === (string) $optionKey)>{{ __($option) }}</option>
                                            @endforeach
                                        </x-select>
                                        @break
                                    @case('bool')
                                        <x-select id="filter-{{ $key }}" name="{{ $name }}">
                                            <option value="">{{ __('Default') }}</option>
                                            @foreach ($filter['options'] as $optionKey => $option)
                                                @php
                                                    $value = (string) (int) ($optionKey == 0);
                                                @endphp

                                                <option value="{{ $value }}" @selected($selected === $value)>{{ __($option) }}</option>
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
                            <x-select id="perPage" name="perPage">
                                <option value="" @selected($criteria->perPage === 25)>25</option>
                                <option value="50" @selected($criteria->perPage === 50)>50</option>
                                <option value="100" @selected($criteria->perPage === 100)>100</option>
                            </x-select>
                        </div>
                    </x-slot:content>
                </x-dropdown>
            @endif

            @if (!empty($rightBarButtonItems))
                {{ $rightBarButtonItems }}
            @endif

            {{-- Lettered Index --}}
            <x-select width="" name="letter">
                <option value="">{{ __('All') }}</option>
                @foreach ($criteria->letteredIndex() as $optionKey => $option)
                    <option value="{{ $option }}" @selected($criteria->letter === $option)>{{ __($optionKey) }}</option>
                @endforeach
            </x-select>
        </div>
    </div>

    <x-hr class="mt-4 ml-4 mr-4" />

    @if (!empty($criteria->searchTypes))
        <input type="hidden" name="type" value="{{ $criteria->type === $criteria->defaultType ? '' : $criteria->type }}">

        <div class="flex gap-2 pt-4 pb-4 pl-4 pr-4 whitespace-nowrap overflow-x-scroll no-scrollbar">
            @foreach ($criteria->searchTypes as $value)
                @php
                    $slug = str($value)->slug()->value();
                    $slug = $slug === 'all' ? '' : $slug;
                @endphp

                <x-toggle-button :selected="$criteria->type === $slug" value="{{ $slug === $criteria->defaultType ? '' : $slug }}" data-search-choice="type">{{ __($value) }}</x-toggle-button>
            @endforeach
        </div>
    @endif
</form>
