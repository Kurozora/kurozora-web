<div data-section>
    <section class="relative pb-6 mb-8 z-10">
        <x-section-nav class="flex flex-nowrap justify-between mb-5 xl:safe-area-inset-scroll">
            <x-slot:title>
                {{ __('Feed') }}
            </x-slot:title>

            <x-slot:action>
                @hasrole('superAdmin')
                    <x-button data-section-refresh="{{ $refreshUrl }}">{{ __('Refresh') }}</x-button>
                @endhasrole
            </x-slot:action>
        </x-section-nav>

        <div class="flex flex-col gap-6 pl-4 pr-4 xl:safe-area-inset-scroll">
            @foreach ($feedMessages as $feedMessage)
                <x-feed.message-lockup :feed-message="$feedMessage" />
            @endforeach
        </div>

        <div class="mt-4 pl-4 pr-4 xl:safe-area-inset-scroll">
            {{ $feedMessages->links() }}
        </div>
    </section>
</div>
