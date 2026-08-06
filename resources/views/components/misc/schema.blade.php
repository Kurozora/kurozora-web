@props(['data' => []])

@php
    // Concatenate to dodge Blade's @context directive parser.
    $schemaPayload = ['@' . 'context' => 'https://schema.org/'] + $data;
    $encodedSchema = json_encode($schemaPayload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
@endphp

@if ($encodedSchema !== false)
    <script type="application/ld+json">{!! $encodedSchema !!}</script>
@endif
