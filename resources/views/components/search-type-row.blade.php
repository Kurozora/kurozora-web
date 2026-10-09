@props(['criteria'])

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
