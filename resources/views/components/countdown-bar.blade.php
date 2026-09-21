@props(['caption', 'schedule' => null, 'timestamp', 'anchor'])

<a
    href="{{ $anchor }}"
    {{ $attributes->merge(['class' => 'block bg-secondary rounded-lg no-external-icon']) }}
    x-data="{
        targetAt: {{ $timestamp }} * 1000,
        days: 0,
        hours: 0,
        minutes: 0,
        seconds: 0,
        tick() {
            let remaining = Math.max(0, Math.floor((this.targetAt - Date.now()) / 1000))

            this.days = Math.floor(remaining / 86400)
            this.hours = Math.floor((remaining % 86400) / 3600)
            this.minutes = Math.floor((remaining % 3600) / 60)
            this.seconds = remaining % 60
        },
    }"
    x-init="() => {
        tick()

        setInterval(() => {
            tick()
        }, 1000)
    }"
>
    <div class="flex flex-row items-center pt-4 pr-4 pb-4 pl-4">
        <div class="hidden md:flex flex-col pr-4 border-r border-primary">
            <p class="text-sm text-secondary uppercase">{{ $caption }}</p>

            @if (!empty($schedule))
                <p class="font-semibold">{{ $schedule }}</p>
            @endif
        </div>

        <div class="flex flex-grow">
            <div class="flex flex-col flex-1 items-center pr-3 pl-3">
                <p class="font-bold text-2xl" x-text="days"></p>
                <p class="text-sm text-secondary">{{ __('days') }}</p>
            </div>

            <div class="flex flex-col flex-1 items-center pr-3 pl-3 border-l border-primary">
                <p class="font-bold text-2xl" x-text="hours"></p>
                <p class="text-sm text-secondary">{{ __('hrs') }}</p>
            </div>

            <div class="flex flex-col flex-1 items-center pr-3 pl-3 border-l border-primary">
                <p class="font-bold text-2xl" x-text="minutes"></p>
                <p class="text-sm text-secondary">{{ __('min') }}</p>
            </div>

            <div class="flex flex-col flex-1 items-center pr-3 pl-3 border-l border-primary">
                <p class="font-bold text-2xl" x-text="seconds"></p>
                <p class="text-sm text-secondary">{{ __('sec') }}</p>
            </div>
        </div>
    </div>
</a>
