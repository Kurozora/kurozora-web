<div class="sm:flex items-center mt-4">
    <div class="flex-1 mb-4">
        <x-input id="search" type="text" placeholder="{{ __('I’m searching for…') }}" wire:model.debounce.500ms="search" />
    </div>

    <div class="flex flex-1 items-center justify-end space-x-1">
        <x-spinner />

        @if (!empty($rightBarButtonItems))
            {{ $rightBarButtonItems }}
        @endif

        {{-- Order --}}
        @if (!empty($this->order))
            <x-dropdown align="right" width="48">
                <x-slot:trigger>
                    <x-square-button>
                        @svg('arrow_up_arrow_down_circle', 'fill-current', ['aria-labelledby' => 'filter', 'width' => '28'])
                    </x-square-button>
                </x-slot>

                <x-slot:content>
                    @foreach($this->order as $order)
                        @if($order['selected'])
                            {{-- Reset Order --}}
                            <button class="block w-full px-4 py-2 bg-gray-100 text-xs text-center text-gray-400 font-semibold hover:bg-gray-50 focus:bg-gray-200" wire:click="resetOrder">
                                {{ __('Reset Order') }}
                            </button>
                            @break
                        @endif
                    @endforeach

                    @foreach($this->order as $key => $order)
                        <div class="block px-4 py-2 bg-gray-100 text-xs text-gray-400 font-semibold">
                            {{ $order['title'] }}
                        </div>

                        <div class="block px-4 py-2">
                            <x-select id="{{ $key }}" wire:model="order.{{ $key }}.selected">
                                @foreach($order['options'] as $optionKey => $option)
                                    <option value="{{ $option }}">{{ __($optionKey) }}</option>
                                @endforeach
                            </x-select>
                        </div>
                    @endforeach
                </x-slot>
            </x-dropdown>
        @endif

        {{-- Filter --}}
        @if (!empty($this->filter))
            <x-dropdown align="right" width="48">
                <x-slot:trigger>
                    <x-square-button>
                        @svg('line_3_horizontal_decrease_circle', 'fill-current', ['aria-labelledby' => 'filter', 'width' => '28'])
                    </x-square-button>
                </x-slot>

                <x-slot:content>
                    @foreach($this->filter as $filter)
                        @if($filter['selected'] != null)
                            {{-- Reset Order --}}
                            <button class="block w-full px-4 py-2 bg-gray-100 text-xs text-center text-gray-400 font-semibold hover:bg-gray-50 focus:bg-gray-200" wire:click="resetFilter">
                                {{ __('Reset Filters') }}
                            </button>
                            @break
                        @endif
                    @endforeach

                    @foreach($this->filter as $key => $filter)
                        {{-- Per Page --}}
                        <div class="block px-4 py-2 bg-gray-100 text-xs text-gray-400 font-semibold">
                            {{ $filter['title'] }}
                        </div>

                        <div class="block px-4 py-2">
                            @switch($filter['type'])
                                @case('string')
                                    <x-input id="{{ $key }}" type="text" wire:model="filter.{{ $key }}.selected" />
                                    @break
                                @case('number')
                                    <x-input id="{{ $key }}" type="number" wire:model="filter.{{ $key }}.selected" />
                                    @break
                                @case('double')
                                    <x-input id="{{ $key }}" type="number" step="0.01" wire:model="filter.{{ $key }}.selected" />
                                    @break
                                @case('date')
                                    <x-input id="{{ $key }}" type="date" wire:model="filter.{{ $key }}.selected" />
                                    @break
                                @case('duration')
                                    <x-input id="{{ $key }}" type="number" step="1" wire:model="filter.{{ $key }}.selected" />
                                    @break
                                @case('time')
                                    <x-input id="{{ $key }}" type="time" wire:model="filter.{{ $key }}.selected" />
                                    @break
                                @case('day')
                                    <x-select id="{{ $key }}" wire:model="filter.{{ $key }}.selected">
                                        <option value="">{{ __('Default') }}</option>
                                        @foreach(range(1, 31) as $day)
                                            <option value="{{ strlen($day) == 1 ? '0' . $day : $day }}">
                                                {{ strlen($day) == 1 ? '0' . $day : $day }}
                                            </option>
                                        @endforeach
                                    </x-select>
                                    @break
                                @case('month')
                                    <x-select id="{{ $key }}" wire:model="filter.{{ $key }}.selected">
                                        <option value="">{{ __('Default') }}</option>
                                        @foreach(range(1, 12) as $month)
                                            <option value="{{ $month }}">
                                                {{ date('F', strtotime('2018-' . $month)) }}
                                            </option>
                                        @endforeach
                                    </x-select>
                                    @break
                                @case('select')
                                    <x-select id="{{ $key }}" wire:model="filter.{{ $key }}.selected">
                                        <option value="">{{ __('Default') }}</option>
                                        @foreach($filter['options'] as $optionKey => $option)
                                            <option value="{{ $optionKey }}">{{ __($option) }}</option>
                                        @endforeach
                                    </x-select>
                                    @break
                                    @foreach($filter['options'] as $optionKey => $option)
                                        <option value="{{ (int) ($optionKey == 0) }}">{{ __($option) }}</option>
                                    @endforeach
                                @case('bool')
                                    <x-select id="{{ $key }}" wire:model="filter.{{ $key }}.selected">
                                        <option value="">{{ __('Default') }}</option>
                                        @foreach($filter['options'] as $optionKey => $option)
                                            <option value="{{ (int) ($optionKey == 0) }}">{{ __($option) }}</option>
                                        @endforeach
                                    </x-select>
                                    @break
                            @endswitch
                        </div>
                    @endforeach

                    {{-- Per Page --}}
                    <div class="block px-4 py-2 bg-gray-100 text-xs text-gray-400 font-semibold">
                        {{ __('Per Page') }}
                    </div>

                    <div class="block px-4 py-2">
                        <x-select id="perPage" wire:model="perPage">
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </x-select>
                    </div>
                </x-slot>
            </x-dropdown>
        @endif
    </div>
</div>
