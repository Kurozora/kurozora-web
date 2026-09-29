@props(['model'])

@php
    $libraryStatuses = match ($model->getMorphClass()) {
        \App\Models\Anime::class => \App\Enums\UserLibraryStatus::asAnimeSelectArray(),
        \App\Models\Game::class => \App\Enums\UserLibraryStatus::asGameSelectArray(),
        \App\Models\Manga::class => \App\Enums\UserLibraryStatus::asMangaSelectArray(),
    };
    $libraryButton = [
        'type' => $model->getMorphClass(),
        'id' => $model->id,
        'slug' => $model->slug,
        'kind' => class_basename($model->getMorphClass()),
        'status' => auth()->check() ? ($model->library->first()?->status ?? -1) : -1,
        'authenticated' => auth()->check(),
    ];
@endphp

<div
    x-data="libraryButton({{ Js::from($libraryButton) }})"
    x-on:library-updated.window="sync($event.detail)"
    x-on:user-actions-failed.window="busy = false"
>
    <x-select-button rounded="full" chevronClass="w-4 h-4 btn-text-tinted sm:w-6 sm:h-6" class="w-24 pl-3 pr-5 pt-2 pb-2 bg-tint text-xs btn-text-tinted font-semibold border-0 shadow-md hover:bg-tint-800 active:bg-tint focus:ring-0 sm:w-32 sm:pl-3 sm:pr-7" x-model="status" x-on:change="update()" x-bind:disabled="busy">
        <option value="-1" selected hidden disabled>{{ str(__('Add'))->upper() }}</option>

        @foreach ($libraryStatuses as $key => $libraryStatus)
            <option value="{{ $key }}">{{ __($libraryStatus) }}</option>
        @endforeach

        <template x-if="status >= 0">
            <option class="text-red-500" value="-2">{{ __('Remove from Library') }}</option>
        </template>
    </x-select-button>
</div>
