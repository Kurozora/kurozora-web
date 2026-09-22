@props(['lockup' => 'small', 'kind' => \App\Enums\UserLibraryKind::Anime])

<div>
    @if ($lockup === 'banner')
        <x-skeletons.banner-lockup />
    @else
        <section class="pt-4 pb-8">
            <div class="xl:safe-area-inset">
                <x-skeletons.section-nav />
            </div>

            <x-skeletons.lockup-row :lockup="$lockup" :kind="$kind" />
        </section>
    @endif
</div>
