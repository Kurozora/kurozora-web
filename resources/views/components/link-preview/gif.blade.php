@props(['mediaUrl'])

<div
    class="w-full"
    x-data="gifPlayer('{{ $mediaUrl }}')"
>
    <div
        class="relative"
        x-on:pointerdown="onPointerDown"
        x-on:pointerup="onPointerUp"
        x-on:pointerenter="onPointerEnter"
        x-on:pointerleave="onPointerLeave"
    >
        <canvas x-ref="canvas" class="w-full rounded-md"></canvas>

        <div
            x-cloak
            x-show="showControls"
            x-transition.opacity
            class="absolute inset-0 flex flex-col justify-between"
        >
            <div class="absolute top-0 bottom-0 left-0 right-0">
                <div class="flex flex-col justify-center items-center h-full" x-ref="controlOverlay">
                    <button
                        class="inline-flex items-center pl-2 pr-2 pt-2 pb-2 sm:px-4 sm:py-4 bg-blur backdrop-blur border border-transparent rounded-full font-semibold text-xs uppercase tracking-widest shadow-md hover:bg-tint-800 hover:btn-text-tinted active:bg-tint active:btn-text-tinted focus:outline-none disabled:bg-gray-100 disabled:text-gray-300 disabled:cursor-default disabled:opacity-100 transition ease-in-out duration-150"
                        x-on:click="manager.toggle()"
                        x-bind:title="manager?.isPlaying ? '{{ __('Pause') }}' : '{{ __('Play') }}'"
                    >
                        <template x-if="manager?.isPlaying">
                            @svg('pause_fill', 'fill-current', ['width' => '24'])
                        </template>

                        <template x-if="!manager?.isPlaying">
                            @svg('play_fill', 'fill-current', ['width' => '24'])
                        </template>
                    </button>
                </div>
            </div>

            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-2 rounded-md">
                <div class="flex items-center gap-1">
                    <span x-text="manager?.currentTimeFormatted ?? '0:00'"></span>

                    <input class="w-full" type="range" min="0" :max="manager.totalDuration" x-model="manager.elapsedTime" x-on:input="scrub" />

                    <span x-text="manager?.totalTimeFormatted ?? '0:00'"></span>

                    <x-dropdown align="top" width="48" class="right-0">
                        <x-slot:trigger>
                            <x-square-button
                                title="{{ __('Speed') }}"
                            >
                                @svg('gauge_with_dots_needle_67percent', 'text-white fill-current', ['width' => '20'])
                            </x-square-button>
                        </x-slot:trigger>

                        <x-slot:content>
                            <div class="flex flex-col gap-2 pt-2 pb-2 pl-2 pr-2">
                                <div class="flex flex-col gap-1">
                                    <x-radio value="true" x-model="isCustomSpeed">{{ __('Custom') }}<span x-text="'(' + speed + 'x)'"></span></x-radio>

                                    <input class="w-full" type="range" :min="minPlaybackSpeed" :max="maxPlaybackSpeed" step="0.05" x-model="speed" />
                                </div>

                                <template x-for="playbackSpeed in playbackSpeeds">
                                    <x-radio x-bind:value="playbackSpeed.toFixed(2)" x-model="speed" >
                                        <span x-text="playbackSpeed + 'x'"></span>
                                    </x-radio>
                                </template>
                            </div>
                        </x-slot:content>
                    </x-dropdown>
                </div>
            </div>
        </div>
    </div>
</div>
