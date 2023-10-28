<div>
    <x-tinted-pill-button
        color="orange"
        wire:click="toggleFollow"
        :title="$this->isFollowing ? __('Unfollow :x', ['x' => $user->username]) : __('Follow :x', ['x' => $user->username])"
    >
        @auth
            @if ($this->isFollowing)
                {{ __('Following') }}
            @else
                {{ __('Follow') }}
            @endif
        @else
            {{ __('Follow') }}
        @endauth
    </x-tinted-pill-button>
</div>
