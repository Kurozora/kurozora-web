<x-base-layout>
    <x-slot:styles>
        <link rel="preload" href="{{ url(mix('css/chat.css')) }}" as="style">
        <link rel="stylesheet" href="{{ url(mix('css/chat.css')) }}">
    </x-slot:styles>

    <main
        class="mx-auto"
        x-data="{
            messages: JSON.parse(localStorage.getItem('messages' || '[]'))
        }"
        x-init="$watch('messages', function(val) {
            localStorage.removeItem('messages')

            if (val !== null) {
                localStorage.setItem('messages', JSON.stringify(val))
            }
        })"
    >
        <div class="space-x-2 mt-8 mb-8">
            <x-button
                x-on:click="screenshot(document.getElementById('kuroChat1'))"
            >
                {{ __('Screenshot Kuro #1') }}
            </x-button>

            <x-button
                x-on:click="screenshot(document.getElementById('kuroChat2'))"
            >
                {{ __('Screenshot Kuro #2') }}
            </x-button>

            <x-danger-button
                x-on:click="messages = null"
            >
                {{ __('Reset conversation') }}
            </x-danger-button>
        </div>

        <section class="flex space-x-2">
            <div
                id="kuroChat1"
                class="relative flex flex-col border border-primary rounded-lg overflow-hidden"
                style="width: 675px; height: 900px; background: linear-gradient(var(--bg-secondary-color), var(--bg-primary-color));"
                x-data="{
                    chatOwner: 'Kuro #1',
                    newMessageText: '',
                    sendMessage() {
                        if (this.newMessageText !== '') {
                            var newMessageEntry = {
                                id: Math.random(),
                                content: this.newMessageText,
                                created_at: Date.now().toString()
                            }
                            var messages = JSON.parse(localStorage.getItem('messages') ?? '[]')
                            var lastMessageGroup = messages[messages.length - 1]

                            if (lastMessageGroup?.owner === this.chatOwner) {
                                lastMessageGroup.messages.push(newMessageEntry)
                                lastMessageGroup.updated_at = newMessageEntry.created_at
                            } else {
                                let newMessageGroup = {
                                    owner: this.chatOwner,
                                    messages: [{
                                        id: Math.random(),
                                        content: this.newMessageText,
                                        created_at: Date.now().toString()
                                    }],
                                    updated_at: Date.now().toString()
                                }
                                messages.push(newMessageGroup)
                            }

                            this.messages = messages;
                            this.newMessageText = '';
                        }
                    }
                }"
            >
                <div
                    class="absolute inset-0 opacity-25"
                    style="background: url({{ asset('images/static/chatroom_pattern.png') }}); z-index: 0;"
                >
                </div>

                <section id="header" class="flex justify-between bg-primary pl-4 pr-4 py-3 z-10">
                    <div>
                        <button
                            class="flex justify-center text-tint"
                            style="width: 44px; height: 44px;"
                        >
                            @svg('chevron_backward', 'fill-current', ['width' => 20])
                        </button>
                    </div>

                    <div class="flex space-x-2">
                        <div>
                            <div class="flex">
                                <picture class="relative w-full overflow-hidden">
                                    <img class="bg-primary border-2 border-black/5 rounded-full" src="https://via.placeholder.com/44" alt="Profile Image" width="44" height="44">

                                    <div class="absolute top-0 left-0 h-full w-full"></div>
                                </picture>
                            </div>
                        </div>

                        <div>
                            <p class="font-semibold text-xl text-primary" x-text="chatOwner"></p>
                            <p class="font-medium text-sm text-secondary">{{ config('app.name') }} Chat v1.0.0</p>
                        </div>
                    </div>

                    <div>
                        <button
                            class="flex justify-center text-tint"
                            style="width: 44px; height: 44px;"
                        >
                        </button>
                    </div>
                </section>

                <section id="body" class="flex flex-col h-full pl-4 pr-4 py-3 overflow-scroll z-10">
                    <template x-for="(messageGroup, index) in messages ?? []">
                        <div
                            class="space-y-1"
                            :class="messageGroup.owner === chatOwner ? 'flex flex-col items-end' : ''"
                            x-data="{
                                nextMessageGroup: messages[index + 1],
                                chatOwner: chatOwner,
                                messages: messages
                            }"
                        >
                            <div
                                class="flex flex-col space-y-2 w-full"
                                :class="messageGroup.owner === chatOwner ? 'items-end' : 'items-start'"
                            >
                                <template x-for="message in messageGroup.messages">
                                    <div
                                        class="max-w-[50%] rounded-3xl"
                                        :class="isImage(message.content) ? 'flex' : (messageGroup.owner === chatOwner ? 'pl-4 pr-4 py-3 bg-tertiary' : 'pl-4 pr-4 py-3 bg-primary')"
                                        :key="message.id"
                                    >
                                        <template x-if="!isImage(message.content)">
                                            <p class="text-primary leading-tight" x-text="message.content"></p>
                                        </template>

                                        <template x-if="isImage(message.content)">
                                            <picture class="relative w-full overflow-hidden">
                                                <img class="rounded-3xl" :src="message.content" alt="">

                                                <div class="absolute top-0 left-0 h-full w-full"></div>
                                            </picture>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            <template x-if="nextMessageGroup?.owner === chatOwner && nextMessageGroup?.owner !== chatOwner && nextMessageGroup?.owner !== undefined">
                                <p class="pl-4 pr-4 font-semibold text-secondary text-xs">
                                    @svg('double_check_mark', 'fill-current', ['width' => '14'])
                                </p>
                            </template>

                            <template x-if="messageGroup.owner === chatOwner && nextMessageGroup?.owner === undefined">
                                <p class="pl-4 pr-4 font-semibold text-secondary text-xs">
                                    {{ __('Delivered') }}
                                </p>
                            </template>

                            <template x-if="nextMessageGroup?.owner !== chatOwner && nextMessageGroup?.owner !== undefined">
                                <p class="pl-4 pr-4 font-semibold text-secondary text-xs" x-text="toAMPM(messageGroup.updated_at)"></p>
                            </template>
                        </div>
                    </template>
                </section>

                <section id="footer" class="bg-primary pl-4 pr-4 py-3 z-10">
                    <form class="flex space-x-2" @submit.stop.prevent="sendMessage()">
                        <textarea
                            id="messageBox"
                            class="form-text w-full bg-secondary text-primary rounded-3xl border-none outline-none shadow-sm resize-none placeholder:text-secondary hover:resize-y focus:ring-0"
                            style="min-height: 44px;"
                            placeholder="{{ __('Write a message') }}"
                            rows="1"
                            x-model="newMessageText"
                        ></textarea>

                        <div class="flex space-x-2">
                            <x-emoji />

                            <button
                                class="flex justify-center text-tint"
                                style="width: 44px; height: 44px;"
                                type="submit"
                            >
                                @svg('arrow_up_circle_fill', 'fill-current', ['width' => 24])
                            </button>
                        </div>
                    </form>
                </section>
            </div>

            <div
                id="kuroChat2"
                class="relative flex flex-col border border-primary rounded-lg overflow-hidden"
                style="width: 675px; height: 900px; background: linear-gradient(var(--bg-secondary-color), var(--bg-primary-color));"
                x-data="{
                    chatOwner: 'Kuro #2',
                    newMessageText: '',
                    sendMessage() {
                        if (this.newMessageText !== '') {
                            var newMessageEntry = {
                                id: Math.random(),
                                content: this.newMessageText,
                                created_at: Date.now().toString()
                            }
                            var messages = JSON.parse(localStorage.getItem('messages') ?? '[]')
                            var lastMessageGroup = messages[messages.length - 1]

                            if (lastMessageGroup?.owner === this.chatOwner) {
                                lastMessageGroup.messages.push(newMessageEntry)
                                lastMessageGroup.updated_at = newMessageEntry.created_at
                            } else {
                                let newMessageGroup = {
                                    owner: this.chatOwner,
                                    messages: [{
                                        id: Math.random(),
                                        content: this.newMessageText,
                                        created_at: Date.now().toString()
                                    }],
                                    updated_at: Date.now().toString()
                                }
                                messages.push(newMessageGroup)
                            }

                            this.messages = messages;
                            this.newMessageText = '';
                        }
                    }
                }"
            >
                <div
                    class="absolute inset-0 opacity-25"
                    style="background: url({{ asset('images/static/chatroom_pattern.png') }}); z-index: 0;"
                >
                </div>

                <section id="header" class="flex justify-between bg-primary pl-4 pr-4 py-3 z-10">
                    <div>
                        <button
                            class="flex justify-center text-tint"
                            style="width: 44px; height: 44px;"
                        >
                            @svg('chevron_backward', 'fill-current', ['width' => 20])
                        </button>
                    </div>

                    <div class="flex space-x-2">
                        <div>
                            <div class="flex">
                                <picture class="relative w-full overflow-hidden">
                                    <img class="bg-primary border-2 border-black/5 rounded-full" src="https://via.placeholder.com/44" alt="Profile Image" width="44" height="44">

                                    <div class="absolute top-0 left-0 h-full w-full"></div>
                                </picture>
                            </div>
                        </div>

                        <div>
                            <p class="font-semibold text-xl text-primary" x-text="chatOwner"></p>
                            <p class="font-medium text-sm text-secondary">{{ config('app.name') }} Chat v1.0.0</p>
                        </div>
                    </div>

                    <div>
                        <button
                            class="flex justify-center text-tint"
                            style="width: 44px; height: 44px;"
                        >
                        </button>
                    </div>
                </section>

                <section id="body" class="flex flex-col h-full pl-4 pr-4 py-3 overflow-scroll z-10">
                    <template x-for="(messageGroup, index) in messages ?? []">
                        <div
                            class="space-y-1"
                            :class="messageGroup.owner === chatOwner ? 'flex flex-col items-end' : ''"
                            x-data="{
                                nextMessageGroup: messages[index + 1],
                                chatOwner: chatOwner,
                                messages: messages
                            }"
                        >
                            <div
                                class="flex flex-col space-y-2 w-full"
                                :class="messageGroup.owner === chatOwner ? 'items-end' : 'items-start'"
                            >
                                <template x-for="message in messageGroup.messages">
                                    <div
                                        class="max-w-[50%] rounded-3xl"
                                        :class="{ 'flex': isImage(message.content), 'pl-4 pr-4 py-3 bg-tertiary': !isImage(message.content) && messageGroup.owner === chatOwner, 'pl-4 pr-4 py-3 bg-primary': !isImage(message.content) && messageGroup.owner !== chatOwner}"
                                        :key="message.id"
                                    >
                                        <template x-if="!isImage(message.content)">
                                            <p class="text-primary leading-tight" x-text="message.content"></p>
                                        </template>

                                        <template x-if="isImage(message.content)">
                                            <picture class="relative w-full overflow-hidden">
                                                <img class="rounded-3xl" :src="message.content" alt="">

                                                <div class="absolute top-0 left-0 h-full w-full"></div>
                                            </picture>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            <template x-if="nextMessageGroup?.owner === chatOwner && nextMessageGroup?.owner !== chatOwner && nextMessageGroup?.owner !== undefined">
                                <p class="pl-4 pr-4 font-semibold text-secondary text-xs">
                                    @svg('double_check_mark', 'fill-current', ['width' => '14'])
                                </p>
                            </template>

                            <template x-if="messageGroup.owner === chatOwner && nextMessageGroup?.owner === undefined">
                                <p class="pl-4 pr-4 font-semibold text-secondary text-xs">
                                    {{ __('Delivered') }}
                                </p>
                            </template>

                            <template x-if="nextMessageGroup?.owner !== chatOwner && nextMessageGroup?.owner !== undefined">
                                <p class="pl-4 pr-4 font-semibold text-secondary text-xs" x-text="toAMPM(messageGroup.updated_at)"></p>
                            </template>
                        </div>
                    </template>
                </section>

                <section id="footer" class="bg-primary pl-4 pr-4 py-3 z-10">
                    <form class="flex space-x-2" @submit.stop.prevent="sendMessage()">
                        <textarea
                            id="messageBox"
                            class="form-text w-full bg-secondary text-primary rounded-3xl border-none outline-none shadow-sm resize-none placeholder:text-secondary hover:resize-y focus:ring-0"
                            style="min-height: 44px;"
                            placeholder="{{ __('Write a message') }}"
                            rows="1"
                            x-model="newMessageText"
                        ></textarea>

                        <div class="flex space-x-2">
                            <x-emoji />

                            <button
                                class="flex justify-center text-tint"
                                style="width: 44px; height: 44px;"
                                type="submit"
                            >
                                @svg('arrow_up_circle_fill', 'fill-current', ['width' => 24])
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </section>
    </main>

    <x-slot:scripts>
        <script src="{{ url(mix('js/chat.js')) }}"></script>

        <script>
            function isImage(url) {
                return (url.match(/\.(jpeg|jpg|gif|png)$/) != null)
            }

            function toAMPM(dateString) {
                let date = new Date(parseInt(dateString) * 1000)
                let hours = date.getHours()
                let minutes = date.getMinutes()
                let ampm = hours >= 12 ? 'pm' : 'am'
                hours = hours % 12
                hours = hours ? hours : 12 // the hour '0' should be '12'
                minutes = minutes.toString().padStart(2, '0')
                return hours + ':' + minutes + ' ' + ampm
            }
        </script>
    </x-slot:scripts>
</x-base-layout>
