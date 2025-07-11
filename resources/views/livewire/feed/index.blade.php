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

    <x-slot:scripts>
        <script src="{{ url(mix('js/gif.js')) }}"></script>
        <script src="{{ url(mix('js/markdown.js')) }}"></script>
    </x-slot:scripts>

    <div class="pt-4 pb-6" wire:init="loadPage">
        <section class="mb-4">
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

        <section class="flex flex-row gap-4 mt-4 pl-4 pr-4">
            <x-profile-image-view class="w-16 h-16" :user="auth()->user()" />

            <div class="flex flex-col gap-2 w-full">
                <livewire:components.feed-message-composer />

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
{{--                                @if ($linkPreview->image_url)--}}
{{--                                    <img src="{{ $linkPreview->image_url }}" class="mt-2 rounded max-w-2xl" />--}}
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

        @if ($this->feedMessages->count())
            <section class="mt-4 border-t border-primary">
                <div class="flex flex-col">
                    @foreach ($this->feedMessages as $feedMessage)
                        <livewire:components.feed.message-lockup :feed-message="$feedMessage" wire:key="{{ uniqid($feedMessage->id, true) }}" />
                    @endforeach
                </div>

                <div class="mt-4 pl-4 pr-4">
                    {{ $this->feedMessages->links() }}
                </div>
            </section>
       @endif

        {{-- Popup Modal --}}
        @if ($showPopup)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
                    @if ($selectedPopupType === 'edit')
                        <h2 class="text-lg font-bold mb-2">{{ __('Edit Message') }}</h2>
                        <textarea wire:model.defer="message" class="w-full border rounded p-2 mb-4" rows="4"></textarea>
                        <div class="flex justify-end gap-2">
                            <x-button wire:click="closePopup">{{ __('Cancel') }}</x-button>
                            <x-button color="orange" wire:click="confirmEdit">{{ __('Save') }}</x-button>
                        </div>
                    @elseif ($selectedPopupType === 'delete')
                        <h2 class="text-lg font-bold mb-2">{{ __('Delete Message') }}</h2>
                        <p class="mb-4">{{ __('Are you sure you want to delete this message?') }}</p>
                        <div class="flex justify-end gap-2">
                            <x-button wire:click="closePopup">{{ __('Cancel') }}</x-button>
                            <x-button color="red" wire:click="confirmDelete">{{ __('Delete') }}</x-button>
                        </div>
                    @elseif ($selectedPopupType === 'reShare')
                        <h2 class="text-lg font-bold mb-2">{{ __('Re-share Message') }}</h2>
                        <p class="mb-4">{{ __('Do you want to re-share this message?') }}</p>
                        <div class="flex justify-end gap-2">
                            <x-button wire:click="closePopup">{{ __('Cancel') }}</x-button>
                            <x-button color="orange" wire:click="confirmReShare">{{ __('Re-share') }}</x-button>
                        </div>
                    @elseif ($selectedPopupType === 'report')
                        <h2 class="text-lg font-bold mb-2">{{ __('Report Message') }}</h2>
                        <p class="mb-4">{{ __('Are you sure you want to report this message?') }}</p>
                        <div class="flex justify-end gap-2">
                            <x-button wire:click="closePopup">{{ __('Cancel') }}</x-button>
                            <x-button color="red" wire:click="confirmReport">{{ __('Report') }}</x-button>
                        </div>
                    @elseif ($selectedPopupType === 'share')
                        <h2 class="text-lg font-bold mb-2">{{ __('Share Message') }}</h2>
                        <p class="mb-4">{{ __('Share this message with others?') }}</p>
                        <div class="flex justify-end gap-2">
                            <x-button wire:click="closePopup">{{ __('Cancel') }}</x-button>
                            <x-button color="orange" wire:click="confirmShare">{{ __('Share') }}</x-button>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</main>
