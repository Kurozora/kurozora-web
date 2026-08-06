@props(['stats', 'winRate'])

<section class="pt-10 pl-4 pr-4 xl:safe-area-inset-scroll">
    <x-kotodama.stat-tiles :stats="$stats" :winRate="$winRate" />
</section>
