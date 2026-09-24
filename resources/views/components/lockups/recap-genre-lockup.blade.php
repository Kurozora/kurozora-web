@props(['recap', 'shareKey' => null])

@once
    <style>
        .recap-genre-gradients {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            filter: blur(40px) brightness(0.95);
        }

        .recap-genre-gradient {
            position: absolute;
            height: auto;
            background: var(--recap-gradient-fill);
        }

        .recap-genre-gradient1 {
            top: -7%;
            inset-inline-start: -1.5%;
            width: 90%;
            aspect-ratio: 2.831 / 1;
            clip-path: url(#recap-gradient-shape-e);
            filter: brightness(0.9);
        }

        .recap-genre-gradient2 {
            top: -6%;
            inset-inline-start: -5.3%;
            width: 66%;
            aspect-ratio: 1.519 / 1;
            clip-path: url(#recap-gradient-shape-f);
        }

        .recap-genre-gradient3 {
            top: -3.3%;
            inset-inline-start: -2.3%;
            width: 30.3%;
            aspect-ratio: 1.519 / 1;
            clip-path: url(#recap-gradient-shape-f);
        }

        .recap-genre-gradient4 {
            bottom: -69%;
            inset-inline-end: -20%;
            width: 90%;
            aspect-ratio: 1.408 / 1;
            clip-path: url(#recap-gradient-shape-g);
            opacity: 0.37;
        }

        .recap-genre-gradient5 {
            bottom: -13%;
            inset-inline-end: -20%;
            width: 90%;
            aspect-ratio: 3.6 / 1;
            clip-path: url(#recap-gradient-shape-h);
            opacity: 0.41;
        }

        .recap-genre-gradient6 {
            bottom: -40%;
            inset-inline-end: -20%;
            width: 90%;
            aspect-ratio: 1.961 / 1;
            clip-path: url(#recap-gradient-shape-i);
            opacity: 0.21;
        }

        @media (max-width: 999px) {
            .recap-genre-gradients {
                filter: blur(20px) brightness(0.95);
            }

            .recap-genre-gradient1,
            .recap-genre-gradient2 {
                top: -3%;
            }

            .recap-genre-gradient3 {
                top: 0;
            }

            .recap-genre-gradient4 {
                bottom: -32%;
            }

            .recap-genre-gradient5 {
                bottom: -6%;
            }

            .recap-genre-gradient6 {
                bottom: -18%;
            }
        }
    </style>
@endonce

<x-recap-gradient-shapes />

<div
    {{ $attributes->merge(['class' => 'relative pt-5 pl-5 pr-5 pb-5 rounded-xl overflow-hidden']) }}
    style="--recap-gradient-fill: radial-gradient(circle at 72% 24%, rgb(255 255 255 / 30%), rgb(255 255 255 / 8%) 45%, transparent 72%), linear-gradient(135deg, {{ $recap->background_color1 }}, {{ $recap->background_color2 }}); background: linear-gradient(135deg, {{ $recap->background_color1 }}, {{ $recap->background_color2 }}); color: {{ $recap->is_light ? 'rgb(0 0 0 / 55%)' : 'rgb(255 255 255 / 94%)' }};"
>
    <div class="recap-genre-gradients" aria-hidden="true">
        @foreach (range(1, 6) as $gradient)
            <div class="recap-genre-gradient recap-genre-gradient{{ $gradient }}"></div>
        @endforeach
    </div>

    <ol class="relative flex flex-col gap-3">
        @foreach ($recap->recapItems->take(5) as $key => $recapItem)
            <li style="opacity: {{ 1 - ($key * 0.12) }};">
                <a
                    class="flex flex-row items-center gap-4"
                    href="{{ $recapItem->model_type === \App\Models\Theme::class ? route('themes.details', $recapItem->model) : route('genres.details', $recapItem->model) }}"
                    wire:navigate
                >
                    <p class="w-6 text-2xl font-semibold" aria-hidden="true">{{ $key + 1 }}</p>

                    <p class="text-4xl font-bold whitespace-nowrap">{{ $recapItem->model->name }}</p>
                </a>
            </li>
        @endforeach
    </ol>

    @if ($shareKey)
        <div class="relative flex justify-end pt-2">
            <x-recap-share-button :share-key="$shareKey" />
        </div>
    @endif
</div>
