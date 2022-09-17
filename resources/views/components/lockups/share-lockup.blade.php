@props(['link', 'title', 'imageUrl' => null])

<div
    {{ $attributes->merge(['class' => 'flex flex-col gap-4']) }}
    x-data="{
        shareLink: '',
        updateShareLink() {
            if (this.shouldStartAt) {
                let preferredProgress = Math.round(this.preferredProgress)
                this.shareLink = '{{ $link }}' + (preferredProgress ? '?t=' + preferredProgress : '')
            } else {
                this.shareLink = '{{ $link }}'
            }
        },
        convertProgressToTime(progress) {
            if (isNaN(progress)) {
                return '00:00'
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
        shouldStartAt: false
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
        preferredProgress = Math.round(currentProgress)
        displayedProgress = normalizeDisplayedProgress(preferredProgress)
    "
>
    <div class="flex flex-row flex-nowrap gap-4 overflow-scroll no-scrollbar">
        <a
            href="https://api.whatsapp.com/send/?text={{ $link }}&type=custom_url&app_absent=0"
            class="flex flex-col items-center"
            target="_blank"
        >
            @svg('brands-whatsapp', '', ['width' => 64])
            <p class="text-sm whitespace-nowrap">WhatsApp</p>
        </a>

        <a
            href="https://twitter.com/intent/tweet?url={{ $link }}&text={{ $title }}&via={{ config('social.twitter.username') }}&related={{ config('social.twitter.username') }}&hashtags={{ config('app.name') }},{{ config('social.twitter.username') }},anime,"
            class="flex flex-col items-center"
            target="_blank"
        >
            @svg('brands-twitter', '', ['width' => 64])
            <p class="text-sm whitespace-nowrap">Twitter</p>
        </a>

        <a
            href="mailto:?body={{ $link }}"
            class="flex flex-col items-center"
            target="_blank"
        >
            @svg('brands-mail', '', ['width' => 64])
            <p class="text-sm whitespace-nowrap">{{ __('E-mail') }}</p>
        </a>

        <a
            href="https://story.kakao.com/share?url={{ $link }}&feature=share"
            class="flex flex-col items-center"
            target="_blank"
        >
            @svg('brands-kakaotalk', '', ['width' => 64])
            <p class="text-sm whitespace-nowrap">KakaoTalk</p>
        </a>

        <a
            href="https://t.me/share/url?url={{ $link }}&text={{ $title }}"
            class="flex flex-col items-center"
            target="_blank"
        >
            @svg('brands-telegram', '', ['width' => 64])
            <p class="text-sm whitespace-nowrap">Telegram</p>
        </a>

        <a
            href="https://www.reddit.com/submit?url={{ $link }}&feature=share&title={{ $title }}"
            class="flex flex-col items-center"
            target="_blank"
        >
            @svg('brands-reddit', '', ['width' => 64])
            <p class="text-sm whitespace-nowrap">Reddit</p>
        </a>

        <a
            href="https://connect.ok.ru/dk?st.cmd=OAuth2Login&st.layout=w&st.redirect=/dk?cmd=WidgetSharePreview&st.cmd=WidgetSharePreview&st.title={{ $title }}&st.shareUrl={{ $link }}&feature=share&st._wt=1&st.client_id=-1"
            class="flex flex-col items-center"
            target="_blank"
        >
            @svg('brands-odnoklassniki', '', ['width' => 64])
            <p class="text-sm whitespace-nowrap">OK</p>
        </a>

        <a
            href="https://www.pinterest.com/pin/create/button/?url={{ $link }}&feature=share&description={{ $title }}&is_video=true{{ !empty($imageUrl) ? '&media=' . $imageUrl : '' }}"
            class="flex flex-col items-center"
            target="_blank"
        >
            @svg('brands-pinterest', '', ['width' => 64])
            <p class="text-sm whitespace-nowrap">Pinterest</p>
        </a>

        <a
            href="https://www.tumblr.com/widgets/share/tool/preview?shareSource=legacy&canonicalUrl=&url={{ $link }}&feature=share&posttype=video&content={{ $link }}&feature=share&caption={{ $title }}"
            class="flex flex-col items-center"
            target="_blank"
        >
            @svg('brands-tumblr', '', ['width' => 64])
            <p class="text-sm whitespace-nowrap">Tumblr</p>
        </a>

        <a
            href="https://www.linkedin.com/shareArticle?url={{ $link }}&feature=share&title={{ $title }}&source={{ config('app.name') }}"
            class="flex flex-col items-center"
            target="_blank"
        >
            @svg('brands-linkedin', '', ['width' => 64])
            <p class="text-sm whitespace-nowrap">LinkedIn</p>
        </a>

        <a
            href="https://mix.com/add?url={{ $link }}&feature=share"
            class="flex flex-col items-center"
            target="_blank"
        >
            @svg('brands-mix', '', ['width' => 64])
            <p class="text-sm whitespace-nowrap">Mix</p>
        </a>

        <a
            href="https://blog.goo.ne.jp/portal_login/blogparts?key=9dgmp3KmwGg&title={{ $title }}&type={{ config('app.name') }}"
            class="flex flex-col items-center"
            target="_blank"
        >
            @svg('brands-goo', '', ['width' => 64])
            <p class="text-sm whitespace-nowrap">goo</p>
        </a>
    </div>

    <div class="flex gap-2">
        <x-input id="link" x-model="shareLink" readonly />

        <x-button
            x-data="{
                copyTextToClipboard() {
                    let input = document.querySelector('#link')

                    navigator.clipboard.writeText(input.value).then(function() {
                        console.log('Async: Copying to clipboard was successful!')
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

    <div class="flex gap-2">
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
</div>
