@props(['word'])

@switch($word->getSubjectKind())
    @case('shows')
        <x-lockups.small-lockup :anime="$word->subject" :isRow="false" />
        @break
    @case('literatures')
        <x-lockups.small-lockup :manga="$word->subject" :isRow="false" />
        @break
    @case('games')
        <x-lockups.small-lockup :game="$word->subject" :isRow="false" />
        @break
    @case('characters')
        <x-lockups.character-lockup :character="$word->subject" :isRow="false" />
        @break
    @case('people')
        <x-lockups.person-lockup :person="$word->subject" :isRow="false" />
        @break
    @case('studios')
        <x-lockups.studio-lockup :studio="$word->subject" :isRow="false" />
        @break
    @case('songs')
        <x-lockups.music-lockup :song="$word->subject" :isRow="false" :showEpisodes="false" />
        @break
@endswitch
