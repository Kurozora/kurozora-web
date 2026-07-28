<main>
    <x-slot:title>
        {{ $title }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Guess the hidden anime word in six tries.') }}
    </x-slot:description>

    <x-slot:appArgument>
        {{ $appArgument }}
    </x-slot:appArgument>

    <x-back-link :url="route('kotodama.daily')" :label="__('Kotodama')" :title="$title" />

    <div class="pb-10">
        <x-kotodama.puzzle
            :game="$game"
            :mode="$mode"
            :shareText="$this->shareText()"
            :flash="$flash"
        />
    </div>
</main>
