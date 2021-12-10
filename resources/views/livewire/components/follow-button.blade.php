<div>
    <x-button wire:click="toggleFollow">
        @if ($user->followers()->where('user_id', Auth::user()->id)->exists())
            {{ __('✓︎ Following') }}
        @else
            {{ __('+ Follow') }}
        @endif
    </x-button>
</div>
