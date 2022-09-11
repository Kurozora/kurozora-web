<div
    class="relative flex flex-col h-full w-full"
    x-data="{
        selectedChatOption: $persist(@entangle('selectedChatOption'))
    }"
>
    {{-- Header --}}
    <div class="w-full bg-gray-50">
        <div class="flex justify-between gap-4 pt-2 pr-2 pb-2 pl-2">
            <div class="flex gap-2" style="width: 44px;">
                @hasrole('superAdmin')
                    <x-square-button
                        wire:click="$refresh"
                    >
                        @svg('arrow_clockwise', 'fill-current', ['width' => '24'])
                    </x-square-button>
                @endhasrole
            </div>

            <div class="flex flex-row justify-center gap-4">
                @foreach([__('Live'), __('Top')] as $chatOptionKey => $chatOptionValue)
                    <template x-if="parseInt(selectedChatOption) === {{ $chatOptionKey }}">
                        <x-button>{{ $chatOptionValue }}</x-button>
                    </template>

                    <template x-if="parseInt(selectedChatOption) !== {{ $chatOptionKey }}">
                        <x-outlined-button x-on:click="selectedChatOption = '{{ $chatOptionKey }}'">{{ $chatOptionValue }}</x-outlined-button>
                    </template>
                @endforeach
            </div>

            <div class="flex gap-2" style="width: 44px;">
                <x-dropdown align="right" width="48">
                    <x-slot:trigger>
                        <x-square-button>
                            @svg('ellipsis', 'fill-current', ['width' => '24'])
                        </x-square-button>
                    </x-slot:trigger>

                    <x-slot:content>
                        <div class="block pl-4 pr-4 pt-2 pb-2 bg-gray-100 text-xs text-gray-400 font-semibold">
                            {{ __('Display') }}
                        </div>

                        <div class="block flex flex-col pl-4 pr-4 pt-2 pb-2">
                            @foreach([__('Cozy'), __('Compact')] as $optionKey => $option)
                                <x-radio value="{{ $optionKey }}" name="selectedCommentDisplayOption" wire:model="selectedCommentDisplayOption">
                                    {{ $option }}
                                </x-radio>
                            @endforeach
                        </div>

                        <div class="block pl-4 pr-4 pt-2 pb-2 bg-gray-100 text-xs text-gray-400 font-semibold">
                            {{ __('Time') }}
                        </div>

                        <div class="block pl-4 pr-4 pt-2 pb-2">
                            <x-checkbox wire:model="showTime">{{ __('Show') }}</x-checkbox>
                        </div>
                    </x-slot:content>
                </x-dropdown>
            </div>
        </div>

        <x-hr />
    </div>

    {{-- Body --}}
    <div class="flex flex-col flex-col-reverse gap-2 pt-2 pr-2 pb-2 pl-2 h-full overflow-scroll z-10">
        @foreach($this->comments as $comment)
            <div class="flex flex-row gap-2">
                @if ($selectedCommentDisplayOption === 0)
                    <x-profile-image-view class="w-16 h-16" :user="$comment->user" />

                    <div class="flex flex-col items-baseline">
                        <div class="flex gap-2">
                            <x-simple-link class="text-sm font-semibold text-gray-500 whitespace-nowrap" href="{{ route('profile.details', $comment->user) }}">{{ $comment->user->username }}</x-simple-link>

                            @if ($showTime)
                                <p class="text-sm text-gray-500 whitespace-nowrap">{{ $comment->created_at->format('g:i A') }}</p>
                            @endif
                        </div>

                        <p style="word-break: break-word;">{!! nl2br(e($comment->content)) !!}</p>
                    </div>
                @else
                    <div class="flex flex-row items-baseline gap-2">
                        @if ($showTime)
                            <p class="text-sm text-gray-500 whitespace-nowrap">{{ $comment->created_at->format('g:i A') }}</p>
                        @endif

                        <x-simple-link class="text-sm font-semibold text-gray-500 whitespace-nowrap" href="{{ route('profile.details', $comment->user) }}">{{ $comment->user->username }}</x-simple-link>
                        <p style="word-break: break-word;">{!! nl2br(e($comment->content)) !!}</p>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Footer --}}
    <div class="bg-gray-50 z-10">
        <x-hr />

        <form
            class="flex flex-row pt-2 pr-2 pb-2 pl-2"
            wire:submit.prevent="postComment"
            x-data="{
                comment: $persist(@entangle('comment').defer).as('_x_comment' + window.location.pathname.replaceAll('/', '_'))
            }"
        >
            @csrf

            <x-comment-textarea
                id="comment-box-{{ $this->id }}"
                wire:submit.prevent="postComment"
                x-on:keydown.enter.prevent="
                    if ($event.shiftKey) {
                        $event.target.value = $event.target.value + '\n'
                        resize()
                        $el.scrollTop = $el.scrollHeight;
                        return
                    }
                    $el.dispatchEvent(new Event('submit'))
                "
                x-model="comment"
            />

            <div class="flex items-end">
                <x-emoji id="{{ $this->id }}" />

                <button
                    class="flex justify-center text-orange-500"
                    style="width: 44px; height: 44px;"
                    wire:click="postComment"
                    x-bind:disabled="!comment"
                >
                    @svg('arrow_up_circle_fill', 'fill-current', ['width' => 24])
                </button>
            </div>
        </form>
    </div>
</div>
