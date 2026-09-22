@props(['kind' => \App\Enums\UserLibraryKind::Anime])

<section class="pb-8 xl:safe-area-inset">
    <x-skeletons.section-nav />

    <x-skeletons.lockup-row :kind="$kind" :safe-area-inset-enabled="false" />
</section>
