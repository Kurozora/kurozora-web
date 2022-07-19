<div>
    <x-rows.user-lockup :users="$this->followings" :is-row="false" />

    <div class="mt-4">
        {{ $this->followings->links() }}
    </div>
</div>
