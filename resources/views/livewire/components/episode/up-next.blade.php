<section>
    <x-section-nav>
        <x-slot:title>
            {{ __('Up Next') }}
        </x-slot:title>
    </x-section-nav>

    <x-rows.episode-lockup :episodes="collect([$this->nextEpisode])" :is-row="false" />
</section>
