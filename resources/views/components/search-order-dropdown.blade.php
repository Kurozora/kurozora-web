@props(['criteria'])

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
