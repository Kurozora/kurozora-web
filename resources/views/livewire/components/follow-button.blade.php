<div>
    <x-button wire:click="toggleFollow">
        @auth
            @if ($user->followers()->where('user_id', auth()->user()->id)->exists())
                {{ __('✓︎ Following') }}
            @else
                {{ __('+ Follow') }}
            @endif
        @else
            {{ __('+ Follow') }}
        @endauth
    </x-button>
</div>
