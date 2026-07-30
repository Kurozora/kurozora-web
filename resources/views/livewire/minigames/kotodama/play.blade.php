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

    @if($mode->is(\App\Enums\Minigames\Kotodama\GameMode::Archive))
        <x-back-link :url="route('kotodama.archive')" :label="__('Kotodama Archive')" :title="$title" />
    @else
        <x-back-link :url="route('kotodama.daily')" :label="__('Kotodama')" :title="$title" />
    @endif

    <div class="pb-10">
        <x-kotodama.puzzle
            :game="$game"
            :mode="$mode"
            :shareText="$this->shareText()"
            :flash="$flash"
        />
    </div>
</main>
