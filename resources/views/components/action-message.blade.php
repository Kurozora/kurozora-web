@props(['on', 'timeout', 'isEphemeral' => true])

<div
    x-data="{
        shown: false,
        timeout: null,
        isEphemeral: @js($isEphemeral),
        showMessage() {
            clearTimeout(this.timeout)

            this.shown = true

            if (this.isEphemeral) {
                this.timeout = setTimeout(() => {
                    this.shown = false
                }, {{ $timeout ?? 2000 }})
            }
        }
    }"
    x-init="$wire.on(@js($on), () => showMessage())"
    x-show="shown"
    x-transition:leave.opacity.duration.1500ms
    x-cloak
    {{ $attributes->merge(['class' => 'text-sm text-secondary']) }}
>
    {{ $slot ?? 'Saved.' }}
</div>
