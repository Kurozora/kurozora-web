<div data-section data-section-url="{{ $refreshUrl }}" data-section-refresh-on="kotodama-finished">
    @auth
        <x-kotodama.streak :stats="$stats" :winRate="$winRate" />
    @endauth

    @if($game)
        <x-kotodama.leaderboard-peek :topEntries="$topEntries" />
    @endif
</div>
