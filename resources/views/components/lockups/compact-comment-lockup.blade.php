@props(['comment', 'showTime' => true])

<div
    {{ $attributes->merge(['class' => 'relative flex flex-row gap-2 pr-2 pl-2']) }}
    x-data="{
        showOptions: false
    }"
    x-on:mouseover="showOptions = true"
    x-on:mouseleave="showOptions = false"
    wire:key="{{ md5($comment->created_at) }}"
>
    <div class="flex flex-row items-baseline gap-2">
        @if ($showTime)
            <p class="text-sm text-gray-500 whitespace-nowrap" title="{{ $comment->created_at }}">{{ $comment->created_at->format('g:i A') }}</p>
        @endif

        <x-simple-link class="text-sm font-semibold text-gray-500 whitespace-nowrap" href="{{ route('profile.details', $comment->user) }}">{{ $comment->user->username }}</x-simple-link>
        <p style="word-break: break-word;">{!! nl2br(e($comment->content)) !!}</p>
    </div>

    <div
        class="absolute flex right-0 bg-gray-50 rounded-md border mr-2"
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
