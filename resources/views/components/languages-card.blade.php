@props(['model', 'id' => 'languages', 'groups' => []])

@php
    $languageGroups = collect($groups)->filter(fn ($languages) => $languages->isNotEmpty());
    $primary = $model->primaryLanguage();
    $originCode = $model->originLanguage();
    $viewerCode = icu_locale();
    $isViewerLanguage = $primary?->code === $viewerCode;
    $modalID = $id . '-modal';

    $rows = $languageGroups
        ->flatMap(fn ($languages, $label) => $languages->map(fn ($language) => ['language' => $language, 'label' => $label]))
        ->groupBy(fn ($row) => $row['language']->id)
        ->map(fn ($grouped) => [
            'language' => $grouped->first()['language'],
            'labels' => $grouped->pluck('label')->unique()->values(),
        ])
        ->sortBy(fn ($row) => [
            $row['language']->code === $primary?->code ? 0 : ($row['language']->code === $originCode ? 1 : 2),
            $row['language']->name,
        ])
        ->values();

    $primaryLabels = $rows->first()['labels'] ?? collect();
@endphp

@if ($rows->isNotEmpty())
    <x-information-list
        :id="$id"
        :title="__('Languages')"
        :icon="asset('images/symbols/character_bubble.svg')"
        class="cursor-pointer"
        role="button"
        tabindex="0"
        x-on:click="$dispatch('open-modal', { id: '{{ $modalID }}' })"
        x-on:keydown.enter="$dispatch('open-modal', { id: '{{ $modalID }}' })"
    >
        <x-slot:information>
            {{ $primary?->name ?: '-' }}
        </x-slot:information>

        @if (!$isViewerLanguage)
            <p class="text-sm text-secondary">{{ __('Not available in your language.') }}</p>
        @elseif ($languageGroups->count() > 1)
            <p class="text-sm">{{ $primaryLabels->join(', ', ' ' . __('and') . ' ') }}</p>
        @endif

        <x-slot:footer>
            {{ trans_choice('{0} No other languages|{1} :x other language|[2,*] :x other languages', $rows->count() - 1, ['x' => $rows->count() - 1]) }}
        </x-slot:footer>
    </x-information-list>

    <x-dialog-modal :id="$modalID" maxWidth="md" stickyHeader>
        <x-slot:title>
            {{ __('Languages') }}
        </x-slot:title>

        <x-slot:description>
            {{ trans_choice('{1} :x language|[2,*] :x languages', $rows->count(), ['x' => $rows->count()]) }}
        </x-slot:description>

        <x-slot:content>
            @foreach ($rows as $row)
                <div @class(['pt-3 pr-4 pb-3 pl-4', 'border-t border-primary' => !$loop->first])>
                    <p class="flex flex-wrap items-center gap-2 font-semibold">
                        {{ $row['language']->name }}

                        @if ($row['language']->code === $viewerCode)
                            <span class="text-tint text-xs font-normal">{{ __('Your language') }}</span>
                        @elseif ($row['language']->code === $originCode)
                            <span class="text-secondary text-xs font-normal">{{ __('Original') }}</span>
                        @endif
                    </p>

                    @if ($languageGroups->count() > 1)
                        <div class="flex flex-wrap gap-1 mt-1.5">
                            @foreach ($row['labels'] as $label)
                                <span class="pt-0.5 pr-1.5 pb-0.5 pl-1.5 bg-secondary text-xs rounded">{{ $label }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </x-slot:content>

        <x-slot:footer>
            <x-button x-on:click="$dispatch('close-modal', { id: '{{ $modalID }}' })">{{ __('Done') }}</x-button>
        </x-slot:footer>
    </x-dialog-modal>
@endif
