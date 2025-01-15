<div
    class="relative flex flex-col h-full w-full"
    x-data="{
        selectedChatOption: $persist(@entangle('selectedChatOption').live)
    }"
>
    {{-- Header --}}
    <div class="w-full">
        <div class="flex justify-between gap-4 pt-2 pr-2 pb-2 pl-2">
            <div class="flex gap-2" style="width: 44px;">
                @hasrole('superAdmin')
                    <x-square-button wire:click="$refresh">
                        @svg('arrow_clockwise', 'fill-current', ['width' => '24'])
                    </x-square-button>
                @endhasrole
            </div>

            <div class="flex flex-row justify-center gap-4">
                @foreach ([__('Live'), __('Top')] as $chatOptionKey => $chatOptionValue)
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
                        <div class="block pl-4 pr-4 pt-2 pb-2 text-xs text-secondary font-semibold">
                            {{ __('Display') }}
                        </div>

                        <div class="flex flex-col pl-4 pr-4 pt-2 pb-2">
                            @foreach ([__('Cozy'), __('Compact')] as $optionKey => $option)
                                <x-radio value="{{ $optionKey }}" name="selectedCommentDisplayOption" wire:model.live="selectedCommentDisplayOption">
                                    {{ $option }}
                                </x-radio>
                            @endforeach
                        </div>

                        <div class="block pl-4 pr-4 pt-2 pb-2 text-xs text-secondary font-semibold">
                            {{ __('Time') }}
                        </div>

                        <div class="block pl-4 pr-4 pt-2 pb-2">
                            <x-checkbox wire:model.live="showTime">{{ __('Show') }}</x-checkbox>
                        </div>
                    </x-slot:content>
                </x-dropdown>
            </div>
        </div>

        <x-hr />
    </div>

    {{-- Body --}}
    <div class="flex flex-col-reverse gap-2 pt-2 pb-2 h-full overflow-scroll z-10">
        @if ($this->comments->count())
            @foreach ($this->comments as $comment)
                @if ($selectedCommentDisplayOption === 0)
                    <x-lockups.cozy-comment-lockup :comment="$comment" :showTime="$showTime" />
                @else
                    <x-lockups.compact-comment-lockup :comment="$comment" :showTime="$showTime" />
                @endif
            @endforeach
        @else
            <div class="flex flex-col justify-center items-center h-full">
                @svg('bubble_left_and_bubble_right', 'fill-current', ['width' => '64'])
                <p>{{ __('Be the first to comment!') }}</p>
            </div>
        @endif
    </div>

    {{-- Footer --}}
    <div class="z-10">
        <x-hr />

        <form
            class="flex flex-row pt-2 pr-2 pb-2 pl-2"
            wire:submit="postComment"
            x-data="{
                comment: $persist(@entangle('comment')).as('_x_comment' + window.location.pathname.replaceAll('/', '_'))
            }"
        >
            @csrf

            <x-comment-textarea
                id="comment-box-{{ $this->getID() }}"
                wire:submit="postComment"
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
                <x-emoji id="{{ $this->getID() }}" />

                <button
                    class="flex justify-center text-tint"
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
