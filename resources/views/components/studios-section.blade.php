<div data-section>
    <section class="pb-8">
        <x-section-nav class="pt-4 xl:safe-area-inset-scroll">
            <x-slot:title>
                {{ __('Studios') }}
            </x-slot:title>

            <x-slot:action>
                <x-spinner :wire-loading-enabled="false" data-section-spinner class="hidden" />

                @hasrole('superAdmin')
                    <x-button data-section-refresh="{{ $refreshUrl }}">{{ __('Refresh') }}</x-button>
                @endhasrole

                <x-section-nav-link href="{{ $seeAllUrl }}">{{ __('See All') }}</x-section-nav-link>
            </x-slot:action>
        </x-section-nav>

        <x-rows.studio-lockup :studios="$studios" />
    </section>
</div>
