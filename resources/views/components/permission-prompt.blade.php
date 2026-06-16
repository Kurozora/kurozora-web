@props(['id', 'icon' => null, 'image' => null, 'badge' => null])

<div
    id="{{ $id }}"
    data-prompt
    class="hidden opacity-0 fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 transition-opacity duration-200 ease-out"
    role="alertdialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}-title"
    aria-describedby="{{ $id }}-message"
>
    <div
        data-prompt-card
        class="opacity-0 scale-95 w-80 rounded-3xl border border-primary bg-blur backdrop-blur shadow-lg transition duration-200 ease-out"
    >
        <div class="pt-4 pl-4 pr-4 pb-4">
            @if ($image)
                <div class="relative mb-6 w-14 h-14">
                    <img src="{{ $image }}" width="112" height="112" alt="" class="w-full h-full">

                    @if ($badge)
                        <img src="{{ $badge }}" width="56" height="56" alt="" class="absolute w-6" style="bottom: -0.25rem; right: -0.25rem">
                    @endif
                </div>
            @elseif ($icon)
                <div class="flex items-center justify-center mb-6 w-14 h-14 rounded-2xl bg-tint">
                    @svg($icon, 'fill-current btn-text-tinted', ['width' => 28])
                </div>
            @endif

            <p id="{{ $id }}-title" class="text-base leading-tight font-semibold text-primary">
                {{ $title }}
            </p>

            <p id="{{ $id }}-message" class="mt-1 text-sm leading-snug text-secondary">
                {{ $message }}
            </p>

            <div class="prompt-actions mt-6">
                {{ $actions }}
            </div>
        </div>
    </div>
</div>
