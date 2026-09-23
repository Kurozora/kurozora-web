@props(['recap'])

@once
    <style>
        .recap-backdrop {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            overflow: clip;
            pointer-events: none;
            z-index: -1;
        }

        .recap-gradients-top {
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: auto;
            aspect-ratio: 1.7 / 1;
            overflow: clip;
            filter: blur(50px);
            pointer-events: none;
        }

        .recap-gradients-bottom {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: auto;
            aspect-ratio: 1.7 / 1;
            overflow: visible clip;
            filter: blur(25px);
            pointer-events: none;
        }

        .recap-gradient-top1 {
            position: absolute;
            top: 9.2%;
            inset-inline-end: 0.5%;
            width: clamp(100px, 32%, 200px);
            height: auto;
            aspect-ratio: 1.365 / 1;
            background: var(--recap-gradient-fill);
            clip-path: url(#recap-gradient-shape-a);
            scale: 2.4;
            transform: scaleY(-1) rotate(169deg);
            transform-origin: center center;
            z-index: 1;
        }

        .recap-gradient-top2 {
            position: absolute;
            top: 5%;
            inset-inline-end: 2%;
            width: clamp(100px, 27%, 200px);
            height: auto;
            aspect-ratio: 2.216 / 1;
            background: var(--recap-gradient-fill);
            clip-path: url(#recap-gradient-shape-b);
            scale: 4;
            transform: scaleX(-1);
            transform-origin: center center;
        }

        .recap-gradient-bottom1 {
            position: absolute;
            bottom: -1.5%;
            inset-inline-end: 29%;
            width: 12.75%;
            height: auto;
            aspect-ratio: 2.535 / 1;
            background: var(--recap-gradient-fill);
            clip-path: url(#recap-gradient-shape-c);
            scale: 7;
            transform: scaleX(-1);
            transform-origin: center center;
            z-index: 1;
        }

        .recap-gradient-bottom2 {
            position: absolute;
            bottom: 12%;
            inset-inline-start: 24%;
            width: 8.9%;
            height: auto;
            aspect-ratio: 2.909 / 1;
            background: var(--recap-gradient-fill);
            clip-path: url(#recap-gradient-shape-d);
            scale: 9;
            transform: scaleY(-1);
            transform-origin: center center;
        }

        @media (max-width: 999px) {
            .recap-gradients-top,
            .recap-gradients-bottom {
                filter: blur(6.5vw);
            }
        }

        @media (min-width: 1260px) {
            .recap-gradient-top1 {
                top: 5%;
                inset-inline-end: 0;
            }
        }

        @media (min-width: 1280px) {
            .recap-gradients-top,
            .recap-gradients-bottom {
                width: calc(100% - var(--sidebar-width));
            }

            .recap-gradients-bottom {
                left: var(--sidebar-width);
            }
        }
    </style>
@endonce

<x-recap-gradient-shapes />

<div {{ $attributes->merge(['class' => 'recap-backdrop']) }} style="--recap-gradient-fill: radial-gradient(circle at 72% 24%, rgb(255 255 255 / 30%), rgb(255 255 255 / 8%) 45%, transparent 72%), linear-gradient(135deg, {{ $recap->background_color1 }}, {{ $recap->background_color2 }});" aria-hidden="true">
    <div class="recap-gradients-top">
        <div class="recap-gradient-top1"></div>
        <div class="recap-gradient-top2"></div>
    </div>

    <div class="recap-gradients-bottom">
        <div class="recap-gradient-bottom1"></div>
        <div class="recap-gradient-bottom2"></div>
    </div>
</div>
