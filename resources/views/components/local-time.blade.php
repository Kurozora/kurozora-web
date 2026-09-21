@props(['timestamp'])

<p
    {{ $attributes->merge(['class' => 'text-sm']) }}
    x-data="{ template: @js(__(':time your time', ['time' => '__TIME__'])), releasedAt: {{ $timestamp }} * 1000 }"
    x-text="template.replace(
        '__TIME__',
        new Intl.DateTimeFormat(undefined, { hour: '2-digit', minute: '2-digit' }).format(new Date(releasedAt))
    )"
>{{ __(':time your time', ['time' => \Carbon\Carbon::createFromTimestamp($timestamp)->inUserTimezone()->format('H:i')]) }}</p>
