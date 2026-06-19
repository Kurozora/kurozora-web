@props(['word'])

<span
    class="lyrics-tile"
    @if ($word['trailingSpace']) data-space @endif
    data-begin="{{ $word['beginMs'] }}"
    data-end="{{ $word['endMs'] }}"
>
    <span class="lyrics-word">{{ $word['text'] }}</span>

    @if (!empty($word['romaji']))
        <span class="lyrics-romaji" data-romaji>{{ $word['romaji'] }}</span>
    @endif
</span>
