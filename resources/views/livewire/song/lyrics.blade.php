<div data-lyrics-root>
    @if ($show)
        @php($lyrics = $this->lyrics)

        <div
            data-lyrics
            data-am-id="{{ $lyrics['amID'] }}"
            data-offset-ms="{{ $lyrics['offsetMs'] }}"
            data-agents="@json($lyrics['agents'])"
            data-label-translation="{{ __('Translation') }}"
            data-label-off="{{ __('Off') }}"
            data-label-pronunciation="{{ __('Pronunciation') }}"
            class="fixed inset-0 z-[1000] flex flex-col"
        >
            <header class="relative flex shrink-0 items-center justify-center h-14 pl-4 pr-4">
                <button type="button" data-lyrics-close class="absolute left-4 inline-flex h-9 w-9 items-center justify-center rounded-full bg-secondary text-primary" title="{{ __('Done') }}">
                    @svg('xmark', 'fill-current', ['width' => 16])
                </button>

                <p class="font-semibold text-primary">{{ __('Lyrics') }}</p>
            </header>

            <div data-lyrics-list class="flex-1 overflow-y-auto no-scrollbar pl-2 pr-2">
                @forelse ($lyrics['items'] as $index => $item)
                    @if ($item['type'] === 'interlude')
                        <div class="lyrics-interlude" data-interlude data-index="{{ $index }}" data-start-ms="{{ $item['startMs'] }}" data-end-ms="{{ $item['endMs'] }}">
                            <span class="lyrics-dots" data-interlude-dots>
                                <span class="lyrics-dot"></span>
                                <span class="lyrics-dot"></span>
                                <span class="lyrics-dot"></span>
                            </span>
                        </div>
                    @else
                        <div
                            class="lyrics-line"
                            data-line
                            data-index="{{ $index }}"
                            @if (!is_null($item['beginMs'])) data-begin="{{ $item['beginMs'] }}" @endif
                            @if (!is_null($item['endMs'])) data-end="{{ $item['endMs'] }}" @endif
                            data-agent="{{ $item['agent'] }}"
                            @if ($item['hasWordTiming']) data-word-timing @endif
                            @if ($item['hasBackground']) data-has-background @endif
                        >
                            <div class="lyrics-main" data-line-main>
                                @foreach ($item['mainWords'] as $word)
                                    <x-music.lyric-word :word="$word" />
                                @endforeach
                            </div>

                            @if ($item['hasBackground'])
                                <div class="lyrics-background" data-line-bg>
                                    @foreach ($item['backgroundWords'] as $word)
                                        <x-music.lyric-word :word="$word" />
                                    @endforeach
                                </div>
                            @endif

                            @foreach ($item['translations'] as $translation)
                                <p class="lyrics-translation" data-translation data-language="{{ $translation['language'] }}" hidden>{{ $translation['text'] }}</p>
                            @endforeach
                        </div>
                    @endif
                @empty
                    <div class="flex h-full flex-col items-center justify-center gap-2 pl-6 pr-6 text-center">
                        <span class="opacity-40">@svg('character_bubble', 'fill-current', ['width' => 48])</span>
                        <p class="font-semibold text-primary">{{ __('Lyrics Unavailable') }}</p>
                        <p class="text-sm text-secondary">{{ __('We couldn’t find lyrics for this song.') }}</p>
                    </div>
                @endforelse
            </div>

            @if (!empty($lyrics['items']))
                <button type="button" data-lyrics-options class="absolute bottom-6 right-6 inline-flex h-11 w-11 items-center justify-center rounded-full bg-secondary text-primary shadow-md" title="{{ __('Options') }}">
                    @svg('character_bubble', 'fill-current', ['width' => 22])
                </button>

                <div data-lyrics-options-menu hidden class="absolute bottom-20 right-6 w-56 rounded-xl border border-primary bg-secondary p-1 shadow-xl"></div>
            @endif
        </div>
    @endif
</div>
