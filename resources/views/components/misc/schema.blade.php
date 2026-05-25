@props(['data' => []])

@php
    // Concatenate to dodge Blade's @context directive parser.
    $schemaPayload = ['@' . 'context' => 'https://schema.org/'] + $data;
@endphp

<script type="application/ld+json">{!! json_encode($schemaPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) !!}</script>
