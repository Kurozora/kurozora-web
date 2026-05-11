<main>
    <x-slot:title>
        {{ __('Community Guidelines') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('The rules for posting on :x, and a quick note on what the app actually is.', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Community Guidelines') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('The rules for posting on :x, and a quick note on what the app actually is.', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('kb.guidelines') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        kb/guidelines
    </x-slot:appArgument>

    <div class="pt-4 pb-6 max-w-full prose prose-theme lg:prose-lg">
        <x-picture class="mb-8 ml-4 mr-4 not-prose">
            <img class="h-32 w-full object-cover object-center rounded-lg shadow-lg sm:h-60" src="{{ asset('images/static/banners/community_guidelines.webp') }}" alt="{{ __('Community Guidelines') }}" />
        </x-picture>

        {{-- Header --}}
        <section class="xl:safe-area-inset">
            <div class="pr-4 pl-4">
                <h1 class="text-2xl font-bold">{{ __('Community Guidelines') }}</h1>

                <p>{{ __('Hi. Welcome to :x.', ['x' => config('app.name')]) }}</p>

                <p>{{ __('Most of these rules are common sense. We’re writing them down because the same handful of things keep coming up. There’s also a bit about what the app actually is, since that’s where most arguments start.') }}</p>

                <x-hr />
            </div>
        </section>

        {{-- What Kurozora is --}}
        <section id="what-kurozora-is" class="xl:safe-area-inset">
            <div class="pr-4 pl-4">
                <h2 class="text-xl font-bold">
                    <a href="#what-kurozora-is">{{ __('What :x is', ['x' => config('app.name')]) }}</a>
                </h2>

                <p>{{ __(':x is a tracker. You log what you’re watching, reading, or playing, and how far you’ve gotten. That’s the core of it.', ['x' => config('app.name')]) }}</p>

                <p>{{ __('There’s also a feed, reviews, lists, and the rest of the social stuff, for people who want to talk about the things they’re into and argue about endings at 2 a.m. That part is optional. The tracker works fine on its own.') }}</p>

                <x-hr />
            </div>
        </section>

        {{-- What Kurozora is not --}}
        <section id="what-kurozora-is-not" class="xl:safe-area-inset">
            <div class="pr-4 pl-4">
                <h2 class="text-xl font-bold">
                    <a href="#what-kurozora-is-not">{{ __('What :x is not', ['x' => config('app.name')]) }}</a>
                </h2>

                <p>{{ __(':x is not a streaming service. We don’t host video, manga, light novels, or scanlations, and we don’t link out to sites that do. That isn’t going to change unless we can source them legally.', ['x' => config('app.name')]) }}</p>

                <p>{{ __('You get metadata, art, and trailers. Where you actually watch or read the thing is between you and whichever legal service carries it where you live.') }}</p>

                <x-hr />
            </div>
        </section>

        {{-- Where can I watch this? --}}
        <section id="where-can-i-watch-this" class="xl:safe-area-inset">
            <div class="pr-4 pl-4">
                <h2 class="text-xl font-bold">
                    <a href="#where-can-i-watch-this">{{ __('“Where can I watch this?”') }}</a>
                </h2>

                <p>{{ __('This is the most common off-topic post on the app, by a wide margin, so it gets its own section.') }}</p>

                <ul class="list-disc">
                    <li>
                        <p>{!! __('Asking where to stream something, where to read a manga, or where to find a free/dubbed/subbed copy of anything doesn’t really fit here. A search engine will give you a better answer in less time.') !!}</p>
                    </li>
                    <li>
                        <p>{{ __('The composer will sometimes warn you before you post something that reads that way. You can still post it. We’re not going to stop you, but most of those posts get no replies, and the ones that keep recurring do get taken down to maintain a certain feed quality.') }}</p>
                    </li>
                    <li>
                        <p>{{ __('Mentioning where a show is legally available, in the middle of a thread that’s actually about the show, is fine. Piracy links depend on whether the website is safe or not. Repeat posters of unsafe websites will get banned.') }}</p>
                    </li>
                </ul>

                <x-hr />
            </div>
        </section>

        {{-- Reviews --}}
        <section id="reviews" class="xl:safe-area-inset">
            <div class="pr-4 pl-4">
                <h2 class="text-xl font-bold">
                    <a href="#reviews">{{ __('Reviews') }}</a>
                </h2>

                <p>{{ __('Reviews are for reviews. Your opinion on the writing, the art, the pacing, the music, whatever you actually care about. A sentence is fine. A paragraph is better.') }}</p>

                <p>{{ __('A review isn’t the place to ask where to watch the show, or to leave one word with no context. Those get buried or removed.') }}</p>

                <x-hr />
            </div>
        </section>

        {{-- Feed behavior --}}
        <section id="feed-behavior" class="xl:safe-area-inset">
            <div class="pr-4 pl-4">
                <h2 class="text-xl font-bold">
                    <a href="#feed-behavior">{{ __('Feed behavior') }}</a>
                </h2>

                <p>{{ __('The feed is a public space. Behave like you would in one.') }}</p>

                <ul class="list-disc">
                    <li>
                        <p>{!! __('<strong>Be civil.</strong> You can disagree without being mean about it. Personal attacks, slurs, threats, doxxing, and pile-ons all get you removed.') !!}</p>
                    </li>
                    <li>
                        <p>{!! __('<strong>Use the spoiler flag.</strong> Anything past the current season or the most recent major release, flag it. The composer has a button for it.') !!}</p>
                    </li>
                    <li>
                        <p>{!! __('<strong>Use the NSFW flag.</strong> If it’s sexual, graphic, or otherwise not for a general audience, mark it. Unflagged stuff gets pulled.') !!}</p>
                    </li>
                    <li>
                        <p>{!! __('<strong>No spam.</strong> Referral links, invite chains, follow-for-follow, crypto, anything that reads like a bot, posting the same message across twenty threads. None of it.') !!}</p>
                    </li>
                    <li>
                        <p>{!! __('<strong>Stay on topic.</strong> The feed is for anime, manga, music, games, and the people who care about them. Political takes, generic memes, and random drama belong on X (Formerly Twitter).') !!}</p>
                    </li>
                </ul>

                <x-hr />
            </div>
        </section>

        {{-- Reporting, blocking, and appeals --}}
        <section id="reporting-blocking-and-appeals" class="xl:safe-area-inset">
            <div class="pr-4 pl-4">
                <h2 class="text-xl font-bold">
                    <a href="#reporting-blocking-and-appeals">{{ __('Reporting, blocking, and appeals') }}</a>
                </h2>

                <ul class="list-disc">
                    <li>
                        <p>{{ __('Report a post from its context menu. Reports are private and go to moderators.') }}</p>
                    </li>
                    <li>
                        <p>{{ __('Block a user from their profile. After that, they can’t follow you, message you, or reply to anything you post.') }}</p>
                    </li>
                    <li>
                        <p>{!! __('If we pull something of yours and you think we got it wrong, reply to the notification or message us on <a href=":discord" target="_blank">Discord</a>. We do read those.', ['discord' => config('social.discord.url')]) !!}</p>
                    </li>
                </ul>

                <x-hr />
            </div>
        </section>

        {{-- The short version --}}
        <section id="the-short-version" class="xl:safe-area-inset">
            <div class="pr-4 pl-4">
                <h2 class="text-xl font-bold">
                    <a href="#the-short-version">{{ __('The short version') }}</a>
                </h2>

                <ul class="list-disc">
                    <li><p>{{ __('Track. Discuss. Review.') }}</p></li>
                    <li><p>{{ __('Don’t ask where to watch.') }}</p></li>
                    <li><p>{{ __('Be decent.') }}</p></li>
                    <li><p>{{ __('Flag spoilers and NSFW.') }}</p></li>
                    <li><p>{{ __('If you’re not sure your post is worth posting, it probably isn’t.') }}</p></li>
                </ul>

                <p>{{ __('Happy tracking 🧡') }}</p>
            </div>
        </section>
    </div>
</main>
