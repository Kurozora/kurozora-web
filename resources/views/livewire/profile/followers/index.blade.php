<div>
    <x-rows.user-lockup :users="$this->followers" :is-row="false" />

    <div class="mt-4">
        {{ $this->followers->links() }}
    </div>
</div>
