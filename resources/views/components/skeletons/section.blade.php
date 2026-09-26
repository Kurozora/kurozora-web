@props(['lockup' => 'small', 'kind' => \App\Enums\UserLibraryKind::Anime, 'isRow' => true, 'count' => null])

<div>
    <section class="pb-8">
        <x-skeletons.section-nav class="xl:safe-area-inset-scroll" />

        <x-skeletons.lockup-row :lockup="$lockup" :kind="$kind" :is-row="$isRow" :count="$count" />
    </section>
</div>
