@props(['editorial'])

<div class="relative flex flex-col gap-2 w-full pl-2 pr-2 pt-2 pb-2 bg-secondary rounded-xl">
    <p class="text-sm font-semibold text-secondary">{{ __('Editor’s Choice') }}</p>

    <div>
        {!! nl2br(e($editorial->body)) !!}
    </div>

    <p class="text-sm text-secondary">{{ $editorial->byline ?: __('The Kurozora Editors') }}</p>
</div>
