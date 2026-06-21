@props(['notification', 'supportsSelect' => false])

@php
    /** @var \App\Models\Notification $notification */
    $destinationUrl = $notification->destinationUrl;
    $isUnread = $notification->isUnread();
    $username = $notification->getData('username');
    $profileImageURL = $notification->getData('profileImageURL')
        ?? 'https://ui-avatars.com/api/?name=' . urlencode((string) $username) . '&color=FFFFFF&background=AAAAAA&length=1&bold=true&size=256';
    $accessibleLabel = $username ?? $notification->localized_type;
@endphp

<article
    class="relative pt-3 pr-3 pb-3"
    @if ($supportsSelect)
        :class="selectMode ? 'pl-8 cursor-pointer select-none' : ''"
        @click="selectMode && toggleSelection('{{ $notification->id }}', {{ $isUnread ? 'true' : 'false' }})"
    @endif
>
    <span
        class="absolute flex items-center justify-center w-4 h-4"
        style="top: 1.5rem; left: 0.5rem; transform: translateY(-50%);"
        aria-hidden="true"
    >
        @if ($supportsSelect)
            <input
                type="checkbox"
                class="shrink-0 form-checkbox h-4 w-4 text-tint rounded shadow-sm focus:border-tint focus:ring-tint cursor-pointer"
                x-show="selectMode"
                :checked="isSelected('{{ $notification->id }}')"
                @click.stop="toggleSelection('{{ $notification->id }}', {{ $isUnread ? 'true' : 'false' }})"
            />
        @endif

        @if ($isUnread)
            <span
                class="block bg-tint aspect-square rounded-full"
                style="width: 0.40rem;"
                @if ($supportsSelect) x-show="!selectMode" @endif
            ></span>
        @endif
    </span>

    <header class="relative z-10 flex items-center justify-between gap-2">
        <div class="flex items-center gap-2 min-w-0">
            <img
                class="shrink-0 w-6 h-6 rounded"
                src="{{ asset('images/symbols/notifications/' . $notification->iconAsset . '.png') }}"
                alt=""
                aria-hidden="true"
                width="24"
                height="24"
            />

            <p class="text-xs font-semibold uppercase tracking-wide text-secondary truncate" title="{{ $notification->localized_type }}">
                {{ $notification->localized_type }}
            </p>
        </div>

        <div class="shrink-0 flex items-center gap-1 text-xs text-secondary">
            <time
                datetime="{{ $notification->created_at->toIso8601String() }}"
                title="{{ $notification->created_at->toDayDateTimeString() }}"
            >
                {{ $notification->created_at->shortRelativeDiffForHumans() }}
            </time>

            @if ($destinationUrl !== null)
                @svg('chevron_forward', 'fill-current opacity-60', ['width' => 10])
            @endif
        </div>
    </header>

    @if ($notification->cellKind === 'icon')
        <div class="flex items-start gap-2 mt-2">
            <picture class="relative shrink-0 w-10 h-10 rounded-full overflow-hidden">
                <img
                    class="w-full h-full object-cover lazyload"
                    data-sizes="auto"
                    data-src="{{ $profileImageURL }}"
                    alt="{{ $username ? $username . ' Profile Image' : '' }}"
                    title="{{ $username }}"
                    width="40"
                    height="40"
                />

                <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-full"></div>
            </picture>

            <div class="flex-1 min-w-0">
                @if ($username !== null)
                    <p class="font-semibold leading-tight truncate" title="{{ $username }}">{{ $username }}</p>
                @endif

                <p class="text-sm leading-snug line-clamp-2" title="{{ $notification->description }}">
                    {{ $notification->description }}
                </p>
            </div>
        </div>
    @else
        <p class="mt-2 pl-8 text-sm leading-snug line-clamp-2" title="{{ $notification->description }}">
            {{ $notification->description }}
        </p>
    @endif

    @if (!$supportsSelect && $destinationUrl !== null)
        <a
            class="absolute inset-0 z-0"
            href="{{ $destinationUrl }}"
            wire:click="markRead('{{ $notification->id }}')"
            wire:navigate
            aria-label="{{ $accessibleLabel }}"
        ></a>
    @endif

    @if ($supportsSelect && $destinationUrl !== null)
        <a
            class="absolute inset-0 z-0"
            x-show="!selectMode"
            href="{{ $destinationUrl }}"
            wire:click="markRead('{{ $notification->id }}')"
            wire:navigate
            aria-label="{{ $accessibleLabel }}"
        ></a>
    @endif
</article>
