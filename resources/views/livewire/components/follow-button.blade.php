<div>
    <x-button wire:click="toggleFollow">
        @auth
            @if ($user->followers()->where('user_id', Auth::user()->id)->exists())
                {{ __('✓︎ Following') }}
            @else
                {{ __('+ Follow') }}
            @endif
        @else
            {{ __('+ Follow') }}
        @endauth
    </x-button>
</div>
