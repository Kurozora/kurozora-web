@props(['link', 'embedLink' => null, 'title', 'imageUrl', 'type'])

<div
    {{ $attributes->merge(['class' => 'flex flex-col gap-4 pb-4']) }}
    x-data="{
        shareLink: '{{ $link }}',
        updateShareLink() {
            let link = this.generateShareLink()

            if ('{{ $type }}' === 'episode' && this.shouldEmbed) {
                this.shareLink = `<iframe scrolling=&quot;no&quot; frameborder=&quot;no&quot; marginwidth=0 marginheight=0 width=&quot;853&quot; height=&quot;480&quot; allowfullscreen=&quot;allowfullscreen&quot; mozallowfullscreen=&quot;mozallowfullscreen&quot; msallowfullscreen=&quot;msallowfullscreen&quot; oallowfullscreen=&quot;oallowfullscreen&quot; webkitallowfullscreen=&quot;webkitallowfullscreen&quot; allow=&quot;autoplay; accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture&quot; src=&quot;` + link + `&quot; title=&quot;{{ $title }}&quot;></iframe>`
            } else if ('{{ $type }}' === 'song' && this.shouldEmbed) {
                this.shareLink = '{{ str(view('components.embeds.song', ['url' => $embedLink, 'title' => $title]))->trim() }}'
            } else {
                this.shareLink = link
            }
        },
        generateShareLink() {
            let link = this.shouldEmbed ? '{{ $embedLink }}' : '{{ $link }}'

            if (this.shouldStartAt) {
                let preferredProgress = Math.round(this.preferredProgress)
                return link + (preferredProgress ? '?t=' + preferredProgress : '')
            }

            return link
        },
        convertProgressToTime(progress) {
            if (isNaN(progress)) {
                return progress
            } else if (progress < 3600) {
                return new Date(progress * 1000).toISOString().substring(14, 19)
            }

            return new Date(progress * 1000).toISOString().substring(11, 19)
        },
        convertTimeToProgress(progress) {
            return +(progress.split(':').reduce((acc,time) => (60 * acc) + +time))
        },
        normalizeDisplayedProgress(progress) {
            return this.convertProgressToTime(progress)
        },
        normalizePreferredProgress(progress) {
            if (typeof(progress) === 'string') {
                return this.convertTimeToProgress(progress)
            }

            return progress
        },
        currentProgress: $persist(0).as('_x_progress' + window.location.pathname.replaceAll('/', '_')),
        preferredProgress: 0,
        displayedProgress: 0,
        shouldStartAt: false,
        shouldEmbed: false,
    }"
    x-init="
        $watch('preferredProgress', function() {
            updateShareLink()
        })
        $watch('displayedProgress', function(value) {
            preferredProgress = normalizePreferredProgress(value)
            document.querySelector('#startsAt').value = normalizeDisplayedProgress(value)
        })
        $watch('shouldStartAt', function() {
            updateShareLink()
        })
        $watch('shouldEmbed', function () {
            updateShareLink()
        })
        preferredProgress = Math.round(currentProgress)
        displayedProgress = normalizeDisplayedProgress(preferredProgress)
    "
>
    <div
        class="flex flex-row flex-nowrap gap-4 pt-4 pl-4 pr-4 overflow-scroll no-scrollbar"
        x-show="!shouldEmbed"
    >
        <div class="relative flex flex-col items-center">
            @svg('brands-whatsapp', '', ['width' => 64])
            <a
                href="https://api.whatsapp.com/send/?text={{ $link }}&type=custom_url&app_absent=0"
                target="_blank"
                class="text-sm whitespace-nowrap no-external-icon"
            >WhatsApp</a>
        </div>

        <div class="relative flex flex-col items-center">
            @svg('brands-twitter', '', ['width' => 64])
            <a
                href="https://twitter.com/intent/tweet?url={{ $link }}&text={{ $title }}&via={{ config('social.twitter.username') }}&related={{ config('social.twitter.username') }}&hashtags={{ config('app.name') }},{{ config('social.twitter.username') }},anime"
                target="_blank"
                class="text-sm whitespace-nowrap no-external-icon"
            >Twitter</a>
        </div>

        <div class="relative flex flex-col items-center">
            @svg('brands-mail', '', ['width' => 64])
            <a
                href="mailto:?body={{ $link }}"
                target="_blank"
                class="text-sm whitespace-nowrap no-external-icon"
            >{{ __('E-mail') }}</a>
        </div>

        <div class="relative flex flex-col items-center">
            @svg('brands-kakaotalk', '', ['width' => 64])
            <a
                href="https://story.kakao.com/share?url={{ $link }}&feature=share"
                target="_blank"
                class="text-sm whitespace-nowrap no-external-icon"
            >KakaoTalk</a>
        </div>

        <div class="relative flex flex-col items-center">
            @svg('brands-telegram', '', ['width' => 64])
            <a
                href="https://t.me/share/url?url={{ $link }}&text={{ $title }}"
                target="_blank"
                class="text-sm whitespace-nowrap no-external-icon"
            >Telegram</a>
        </div>

        <div class="relative flex flex-col items-center">
            @svg('brands-reddit', '', ['width' => 64])
            <a
                href="https://www.reddit.com/submit?url={{ $link }}&feature=share&title={{ $title }}"
                target="_blank"
                class="text-sm whitespace-nowrap no-external-icon"
            >Reddit</a>
        </div>

        <div class="relative flex flex-col items-center">
            @svg('brands-odnoklassniki', '', ['width' => 64])
            <a
                href="https://connect.ok.ru/dk?st.cmd=OAuth2Login&st.layout=w&st.redirect=/dk?cmd=WidgetSharePreview&st.cmd=WidgetSharePreview&st.title={{ $title }}&st.shareUrl={{ $link }}&feature=share&st._wt=1&st.client_id=-1"
                target="_blank"
                class="text-sm whitespace-nowrap no-external-icon"
            >OK</a>
        </div>

        <div class="relative flex flex-col items-center">
            @svg('brands-pinterest', '', ['width' => 64])
            <a
                href="https://www.pinterest.com/pin/create/button/?url={{ $link }}&feature=share&description={{ $title }}&is_video=true{{ !empty($imageUrl) ? '&media=' . $imageUrl : '' }}"
                target="_blank"
                class="text-sm whitespace-nowrap no-external-icon"
            >Pinterest</a>
        </div>

        <div class="relative flex flex-col items-center">
            @svg('brands-tumblr', '', ['width' => 64])
            <a
                href="https://www.tumblr.com/widgets/share/tool/preview?shareSource=legacy&canonicalUrl=&url={{ $link }}&feature=share&posttype=video&content={{ $link }}&feature=share&caption={{ $title }}"
                target="_blank"
                class="text-sm whitespace-nowrap no-external-icon"
            >Tumblr</a>
        </div>

        <div class="relative flex flex-col items-center">
            @svg('brands-linkedin', '', ['width' => 64])
            <a
                href="https://www.linkedin.com/shareArticle?url={{ $link }}&feature=share&title={{ $title }}&source={{ config('app.name') }}"
                target="_blank"
                class="text-sm whitespace-nowrap no-external-icon"
            >LinkedIn</a>
        </div>

        <div class="relative flex flex-col items-center">
            @svg('brands-mix', '', ['width' => 64])
            <a
                href="https://mix.com/add?url={{ $link }}&feature=share"
                target="_blank"
                class="text-sm whitespace-nowrap no-external-icon"
            >Mix</a>
        </div>

        <div class="relative flex flex-col items-center">
            @svg('brands-goo', '', ['width' => 64])
            <a
                href="https://blog.goo.ne.jp/portal_login/blogparts?key=9dgmp3KmwGg&title={{ $title }}&type={{ config('app.name') }}"
                target="_blank"
                class="text-sm whitespace-nowrap no-external-icon"
            >goo</a>
        </div>
    </div>

    <div x-show="shouldEmbed">
        @switch($type)
            @case('episode')
                <iframe scrolling="no" frameborder="no" marginwidth=0 marginheight=0 width="853" height="480" src="{{ $embedLink }}" title="{{ $title }}" class="w-full"></iframe>
                @break
            @case('song')
                <iframe scrolling="no" frameborder="no" style="width:100%;overflow:hidden;background:transparent;" height="240" loading="lazy" src="{{ $embedLink }}" title="{{ $title }}"></iframe>
                @break
        @endswitch
    </div>

    <div class="flex gap-2 pl-4 pr-4">
        <x-input id="link" class="w-full" x-model="shareLink" readonly />

        <x-button
            x-data="{
                copyTextToClipboard() {
                    let input = document.querySelector('#link')

                    navigator.clipboard.writeText(input.value).then(function() {
                    }, function(err) {
                        console.error('Async: Could not copy text: ', err)
                    })
                }
            }"
            x-show="navigator.clipboard"
            x-on:click="copyTextToClipboard()"
        >
            {{ __('Copy') }}
        </x-button>
    </div>

    @if ($type === 'episode')
        <div class="flex gap-2 pl-4 pr-4">
            <x-checkbox x-model="shouldStartAt">
                {{ __('Start at') }}
            </x-checkbox>

            <x-input
                id="startsAt"
                type="text"
                x-model.debounce.750ms="displayedProgress"
                x-bind:disabled="!shouldStartAt"
            />
        </div>
    @endif

    @if (!empty($embedLink))
        <div class="flex gap-2 pl-4 pr-4">
            <x-checkbox x-model="shouldEmbed">
                {{ __('Embed') }}
            </x-checkbox>
        </div>
    @endif
</div>
