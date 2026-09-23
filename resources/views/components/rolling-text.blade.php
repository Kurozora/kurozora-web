@props(['text'])

@once
    <style>
        @keyframes rolling-text-letter {
            from {
                transform: translateY(100%);
            }

            to {
                transform: translateY(0);
            }
        }

        .rolling-text-letter {
            display: inline-block;
            overflow: clip;
            vertical-align: bottom;
        }

        .rolling-text-letter > span {
            display: inline-block;
            white-space: pre;
            transform: translateY(100%);
        }

        .rolling-text--visible .rolling-text-letter > span {
            animation: rolling-text-letter 0.9s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @media (prefers-reduced-motion: reduce) {
            .rolling-text-letter > span,
            .rolling-text--visible .rolling-text-letter > span {
                transform: none;
                animation: none;
            }
        }
    </style>
@endonce

<span {{ $attributes }} x-data="{ visible: false }" x-intersect.full.once="visible = true" x-bind:class="{ 'rolling-text--visible': visible }"><span class="sr-only">{{ $text }}</span><span aria-hidden="true">@foreach (mb_str_split($text) as $index => $letter)<span class="rolling-text-letter"><span style="animation-delay: {{ 150 + $index * 70 }}ms;">{{ $letter }}</span></span>@endforeach</span></span>
