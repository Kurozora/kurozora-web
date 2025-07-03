<div
    x-data="{
        observe() {
            let observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        $wire.dispatch('load-more', {cursor: {{ $nextCursor }}})
                        this.$refs.loadTrigger.classList.add('hidden')
                    }
                });
            }, {
                rootMargin: this.$el.offsetHeight / 2 + 'px'
            });

            if (this.$refs.loadTrigger) {
                observer.observe(this.$refs.loadTrigger);
            }
        }
    }"
    x-init="observe"
>
    @foreach ($feedMessages as $feedMessage)
        <livewire:components.feed.message-lockup :feed-message="$feedMessage" wire:key="{{ uniqid($feedMessage->id, true) }}" />
    @endforeach

    @if ($isActive && $hasMore && $nextCursor)
        <div x-ref="loadTrigger"></div>
    @endif
</div>
