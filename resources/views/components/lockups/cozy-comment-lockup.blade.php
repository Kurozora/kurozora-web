@props(['comment', 'showTime' => true])

<div
    {{ $attributes->merge(['class' => 'relative flex flex-row gap-2 pr-2 pl-2']) }}
    x-data="{
        showOptions: false
    }"
    x-on:mouseover="showOptions = true"
    x-on:mouseleave="showOptions = false"
    wire:key="{{ uniqid($comment->created_at, true) }}"
>
    <x-profile-image-view class="w-16 h-16" :user="$comment->user" />

    <div class="flex flex-col items-baseline w-full">
        <div class="flex gap-2 justify-between w-full">
            <div class="flex gap-2">
                <x-simple-link class="text-sm font-semibold text-secondary whitespace-nowrap" href="{{ route('profile.details', $comment->user) }}" wire:navigate>{{ $comment->user->username }}</x-simple-link>

                @if ($showTime)
                    <p class="text-sm text-secondary whitespace-nowrap" title="{{ $comment->created_at }}">{{ $comment->created_at->format('g:i A') }}</p>
                @endif
            </div>

            <div
                class="absolute flex right-0 bg-tertiary rounded-md border border-primary mr-2"
                x-show="showOptions"
                x-cloak=""
                x-on:mouseover="showOptions = true"
                x-on:mouseleave="showOptions = false"
            >
                @if ($comment->user->id === auth()->id())
                    <x-square-button wire:click="removeComment('{{ $comment->id }}')">
                        @svg('trash', 'fill-current text-red-500', ['width' => 18])
                    </x-square-button>
                @endif
            </div>
        </div>

        <p class="w-full" style="word-break: break-word;">{!! nl2br(e($comment->content)) !!}</p>
    </div>
</div>
