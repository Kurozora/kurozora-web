@props(['resource', 'model', 'color' => null, 'disabled' => false])

<x-circle-link href="{{ Nova::path() . '/resources/'. \App\Nova\Anime::uriKey() . '/' . $model->id }}" :color="$color" :disabled="$disabled">
    {{ $slot }}
</x-circle-link>
