<main>
    <x-slot:title>
        {{ __('Feed') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discuss anime, manga, games and more. Check out the forums on :x, the world’s most active online anime and manga community.', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Feed') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discuss anime, manga, games and more. Check out the forums on :x, the world’s most active online anime and manga community.', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('feed.index') }}">
    </x-slot:meta>

    <x-slot:styles>
        <link rel="preload" href="{{ url(mix('css/watch.css')) }}" as="style">
        <link rel="stylesheet" href="{{ url(mix('css/watch.css')) }}">
    </x-slot:styles>

    <x-slot:scripts>
        <script src="{{ url(mix('js/gif.js')) }}"></script>
        <script src="{{ url(mix('js/markdown.js')) }}"></script>
        <script src="{{ url(mix('js/watch.js')) }}"></script>
    </x-slot:scripts>

    <div class="pb-6" wire:init="loadPage">
        <section class="sticky top-0 pt-4 pb-4 backdrop-blur bg-blur z-10">
            <div class="flex gap-1 pl-4 pr-4">
                <div class="flex flex-wrap items-center w-full">
                    <h1 class="text-2xl font-bold">{{ __('Feed') }}</h1>
                </div>

                <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    @hasrole('superAdmin')
                    <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
                    @endhasrole
                </div>
            </div>

            <div>

            </div>
        </section>

        <section class="flex flex-row gap-2 mt-4 pl-4 pr-4">
            <x-profile-image-view class="w-12 h-12" :user="auth()->user()" />

            <div class="flex flex-col gap-2 w-full">
                <livewire:components.feed-message-composer is-reply="false" />

{{--                <x-textarea wire:model.live.debounce.500ms="message" :autoresize="true" />--}}

{{--                @if ($linkPreview)--}}
{{--                    <div class="mt-4 border rounded p-3">--}}
{{--                        @if ($linkPreview->embed_html)--}}
{{--                            {!! $linkPreview->embed_html !!}--}}
{{--                        @else--}}
{{--                            <div>--}}
{{--                                <p class="text-sm text-secondary">{{ $linkPreview->provider }}</p><br>--}}
{{--                                <p class="text-primary">{{ $linkPreview->title }}</p><br>--}}
{{--                                <p class="text-sm text-secondary line-clamp-2">{!! nl2br(e($linkPreview->description)) !!}</p><br>--}}
{{--                                @if ($linkPreview->media_url)--}}
{{--                                    <img src="{{ $linkPreview->media_url }}" class="mt-2 rounded max-w-2xl" />--}}
{{--                                @endif--}}
{{--                            </div>--}}
{{--                        @endif--}}
{{--                    </div>--}}
{{--                @endif--}}

{{--                <div class="flex justify-end">--}}
{{--                    <x-tinted-pill-button--}}
{{--                        :color="'orange'"--}}
{{--                        title="{{ __('Post') }}"--}}
{{--                        wire:loading.attr="disabled"--}}
{{--                    >--}}
{{--                        {{ __('Post') }}--}}
{{--                    </x-tinted-pill-button>--}}
{{--                </div>--}}
            </div>
        </section>

        @if ($this->readyToLoad)
            <section class="mt-4 border-t border-primary">
                <div
                    style="content-visibility: auto;"
                    x-data="{
                        onFocus() {
                            $el.setAttribute('wire:poll.30s', 'pollForNewFeedMessages')
                        },
                        onBlur() {
                            $el.removeAttribute('wire:poll.30s')
                        }
                    }"
                    x-on:focus.window="onFocus()"
                    x-on:blur.window="onBlur()"
                >
                    @if ($newFeedMessagesCount)
                        <div class="text-center">
                            <button wire:click="showNewFeedMessages" class="w-full pt-4 pb-4 font-semibold text-tint border-b border-primary hover:bg-tertiary focus:bg-secondary">
                                {{ trans_choice('{1} Show :x message|[2,*] Show :x messages', $newFeedMessagesCount, ['x' => $newFeedMessagesCount]) }}
                            </button>
                        </div>
                    @endif

                    <div class="flex flex-col-reverse">
                        @foreach($newSections as $key => $section)
                            @if ($section['type'] === 'messages')
                                <livewire:components.feed.list-section
                                    :cursor="$section['cursor']"
                                    :is-active="false"
                                    :count="$section['count']"
                                    :reverse="true"
                                    wire:key="new-section-{{ $key }}"
                                />
                            @elseif ($section['type'] === 'ad')
                                <div class="flex items-center w-full pt-4 pb-4 pl-4 pr-4 bg-secondary rounded-lg">
                                    <p>{{ 'custom component shown here wink wink ;)' }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-col" style="content-visibility: auto;">
                    @foreach ($sections as $key => $section)
                        @if ($section['type'] === 'messages')
                            <livewire:components.feed.list-section
                                :cursor="$section['cursor']"
                                :is-active="$key === $activeSectionKey"
                                wire:key="section-{{ $key }}"
                             />
                        @elseif ($section['type'] === 'ad')
                            <div class="flex items-center w-full pt-4 pb-4 pl-4 pr-4 bg-secondary rounded-lg">
                             <p>{{ 'custom component shown here wink wink ;)' }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif

        <div class="flex justify-center mt-4">
            <x-spinner />
        </div>

        {{-- Popup Modal --}}
        @if ($showPopup)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
                    @if ($selectedPopupType === 'edit')
                        <h2 class="text-lg font-bold mb-2">{{ __('Edit Message') }}</h2>

                        <textarea wire:model.defer="message" class="w-full border rounded p-2 mb-4" rows="4"></textarea>

                        <div class="flex justify-end gap-2">
                            <x-outlined-button wire:click="closePopup" wire:loading.attr="disabled">
                                {{ __('Cancel') }}
                            </x-outlined-button>

                            <x-button color="orange" wire:click="confirmEdit" wire:loading.attr="disabled">
                                {{ __('Save') }}
                            </x-button>
                        </div>
                    @elseif ($selectedPopupType === 'delete')
                        <h2 class="text-lg font-bold mb-2">{{ __('Delete Message') }}</h2>

                        <p class="mb-4">{{ __('Are you sure you want to delete this message?') }}</p>

                        <div class="flex justify-end gap-2">
                            <x-outlined-button wire:click="closePopup" wire:loading.attr="disabled">
                                {{ __('Cancel') }}
                            </x-outlined-button>

                            <x-button color="red" wire:click="confirmDelete" wire:loading.attr="disabled">
                                {{ __('Delete') }}
                            </x-button>
                        </div>
                    @elseif ($selectedPopupType === 'reShare')
                        <h2 class="text-lg font-bold mb-2">{{ __('Re-share Message') }}</h2>

                        <p class="mb-4">{{ __('Do you want to re-share this message?') }}</p>

                        <div class="flex justify-end gap-2">
                            <x-outlined-button wire:click="closePopup" wire:loading.attr="disabled">
                                {{ __('Cancel') }}
                            </x-outlined-button>

                            <x-button color="orange" wire:click="confirmReShare" wire:loading.attr="disabled">
                                {{ __('Re-share') }}
                            </x-button>
                        </div>
                    @elseif ($selectedPopupType === 'report')
                        <h2 class="text-lg font-bold mb-2">{{ __('Report Message') }}</h2>

                        <p class="mb-4">{{ __('Are you sure you want to report this message?') }}</p>

                        <div class="flex justify-end gap-2">
                            <x-outlined-button wire:click="closePopup" wire:loading.attr="disabled">
                                {{ __('Cancel') }}
                            </x-outlined-button>

                            <x-button color="red" wire:click="confirmReport" wire:loading.attr="disabled">
                                {{ __('Report') }}
                            </x-button>
                        </div>
                    @elseif ($selectedPopupType === 'share')
                        <h2 class="text-lg font-bold mb-2">{{ __('Share Message') }}</h2>

                        <p class="mb-4">{{ __('Share this message with others?') }}</p>

                        <div class="flex justify-end gap-2">
                            <x-outlined-button wire:click="closePopup" wire:loading.attr="disabled">
                                {{ __('Cancel') }}
                            </x-outlined-button>

                            <x-button color="orange" wire:click="confirmShare" wire:loading.attr="disabled">
                                {{ __('Share') }}
                            </x-button>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</main>
