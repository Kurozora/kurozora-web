@props(['resource', 'model', 'color' => null, 'disabled' => false])

<x-circle-link href="{{ Nova::path() . '/resources/'. $resource::uriKey() . '/' . $model->id }}" rel="nofollow" :color="$color" :disabled="$disabled">
    {{ $slot }}
</x-circle-link>
