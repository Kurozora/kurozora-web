<div>
    <ul class="m-0">
        @foreach ($this->followers as $key => $follower)
            <li class="rounded-lg">
                <x-lockups.user-lockup :user="$follower" wire:key="{{ uniqid(more_entropy: true) }}" />
            </li>

            @if ($key != $this->followers->count() -1)
                <x-hr class="mt-4 mb-4" />
            @endif
        @endforeach
    </ul>

    <div class="mt-4">
        {{ $this->followers->links() }}
    </div>
</div>
