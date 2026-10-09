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
                <x-search-order-dropdown :criteria="$criteria" />
            @endif

            {{-- Filter --}}
            @if (!empty($criteria->filter))
                <x-search-filter-dropdown :criteria="$criteria" />
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
        <x-search-type-row :criteria="$criteria" />
    @endif
</form>
