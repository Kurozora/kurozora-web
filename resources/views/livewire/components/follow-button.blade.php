<div>
    <x-tinted-pill-button
        color="orange"
        wire:click="toggleFollow"
        :title="$this->isFollowed ? __('Unfollow :x', ['x' => $user->username]) : __('Follow :x', ['x' => $user->username])"
    >
{{--        @auth--}}
            @if ($this->isFollowed)
                {{ __('Following') }}
            @else
                {{ __('Follow') }}
            @endif
{{--        @else--}}
{{--            {{ __('Follow') }}--}}
{{--        @endauth--}}
    </x-tinted-pill-button>
</div>
