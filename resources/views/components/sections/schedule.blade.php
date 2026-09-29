<div data-section>
    <section id="#{{ $date->toDateString() }}" class="pb-8">
        <x-section-nav class="pt-4">
            <x-slot:title>
                {{ $date->format('l') . ' ' . $date->format('M d') . ' (' . $models->count() . ')' }}
            </x-slot:title>

            <x-slot:action>
                <x-spinner :wire-loading-enabled="false" data-section-spinner class="hidden" />

                @hasrole('superAdmin')
                    <x-button data-section-refresh="{{ $refreshUrl }}">{{ __('Refresh') }}</x-button>
                @endhasrole
            </x-slot:action>
        </x-section-nav>

        @switch($type)
            @case(\App\Models\Anime::class)
                <x-rows.small-lockup :animes="$models" :shows-schedule="true" :is-row="false" />
                @break
            @case(\App\Models\Game::class)
                <x-rows.small-lockup :games="$models" :shows-schedule="true" :is-row="false" />
                @break
            @case(\App\Models\Manga::class)
                <x-rows.small-lockup :mangas="$models" :shows-schedule="true" :is-row="false" />
                @break
        @endswitch
    </section>
</div>
