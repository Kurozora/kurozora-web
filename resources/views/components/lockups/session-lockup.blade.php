@props(['session', 'supportsSelect' => false])

<article
    class="relative pt-3 pr-3 pb-3"
    @if ($supportsSelect)
        :class="selectMode ? 'pl-8 cursor-pointer select-none' : ''"
        @click="selectMode && toggleSelection('{{ $session->key }}')"
    @endif
>
    @if ($supportsSelect)
        <span
            class="absolute flex items-center justify-center w-4 h-4"
            style="top: 50%; left: 0.5rem; transform: translateY(-50%);"
            aria-hidden="true"
            x-show="selectMode"
        >
            <input
                type="checkbox"
                class="shrink-0 form-checkbox h-4 w-4 text-tint rounded shadow-sm focus:border-tint focus:ring-tint cursor-pointer"
                :checked="isSelected('{{ $session->key }}')"
                @click.stop="toggleSelection('{{ $session->key }}')"
            />
        </span>
    @endif

    <div class="flex items-center gap-3">
        <span class="inline-flex shrink-0 items-center justify-center w-8 h-8 text-secondary">
            @svg($session->device_symbol, 'w-8 h-8 fill-current')
        </span>

        <div class="min-w-0 flex-1">
            <p class="text-sm text-secondary truncate">{{ $session->full_platform }}</p>

            <p class="text-xs text-secondary truncate">
                @if (filled($session->app_source)){{ $session->app_source }} · @endif
                @if ($session->full_location !== ''){{ $session->full_location }} · @endif
                {{ $session->ip_address }} ·
                @if ($session->is_current)
                    <span class="text-green-500 font-semibold">{{ __('This device') }}</span>
                @else
                    {{ $session->last_activity }}
                @endif
            </p>
        </div>

        {{ $slot }}
    </div>
</article>
