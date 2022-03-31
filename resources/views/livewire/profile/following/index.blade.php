<div>
    <ul class="m-0">
        @foreach ($this->followings as $key => $following)
            <li class="rounded-lg">
                <x-lockups.user-lockup :user="$following" wire:key="{{ uniqid(more_entropy: true) }}" />
            </li>

            @if ($key != $this->followings->count() -1)
                <x-hr class="my-4" />
            @endif
        @endforeach
    </ul>

    <div class="mt-4">
        {{ $this->followings->links() }}
    </div>
</div>
