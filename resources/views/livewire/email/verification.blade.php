<div>
    <style>
        .checkmark {
            border-radius: 50%;
            display: block;
            stroke-width: 2;
            stroke: #FFFFFF;
            stroke-miterlimit: 10;
            box-shadow: inset 0 0 0 #10B981;
            animation: fill 0.4s ease-in-out 0.4s forwards, scale 0.3s ease-in-out 0.9s both;
        }

        .checkmark__circle {
            stroke-dasharray: 332;
            stroke-dashoffset: 332;
            stroke-width: 2;
            stroke-miterlimit: 10;
            stroke: #10B981;
            fill: none;
            animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }

        .checkmark__check {
            transform-origin: 50% 50%;
            stroke-dasharray: 152;
            stroke-dashoffset: 152;
            box-shadow: inset 0 0 0 #FFFFFF;
            fill: transparent;
            animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards, fill-white 0.4s cubic-bezier(0.65, 0, 0.45, 1) 1s forwards;
        }

        @keyframes stroke {
            100% {
                stroke-dashoffset: 0;
            }
        }
        @keyframes scale {
            0%, 100% {
                transform: none;
            }
            50% {
                transform: scale3d(1.1, 1.1, 1);
            }
        }
        @keyframes fill {
            100% {
                box-shadow: inset 0 0 0 100px #10B981;
            }
        }
        @keyframes fill-white {
            100% {
                fill: #FFFFFF;
            }
        }
    </style>

    <x-slot name="title">
        {{ __('Email Verification') }}
    </x-slot>

    <x-slot name="meta">
        <meta name="robots" content="noindex">
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kurozora ID') }}
        </h2>
    </x-slot>

    <div class="flex flex-col items-center mt-20">
        @svg('checkmark_circle', 'checkmark')
        <p class="text-2xl font-bold mt-6">{{ __('Email Address Verified') }}</p>
        <p>{{ $email . ' ' . __('has been verified.') }}</p>
    </div>
</div>
