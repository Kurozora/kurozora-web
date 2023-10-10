<div wire:init="loadSection">
    @if ($this->feedMessages->count())
        <section class="relative max-w-7xl mx-auto pl-4 pr-4 pb-6 mb-8 z-10 sm:px-6">
            <x-section-nav class="flex flex-nowrap justify-between mb-5">
                <x-slot:title>
                    {{ __('Feed') }}
                </x-slot:title>

                <x-slot:action>
                    @hasrole('superAdmin')
                        <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
                    @endhasrole
                </x-slot:action>
            </x-section-nav>

            <div class="flex flex-col gap-6">
                @foreach ($this->feedMessages as $feedMessage)
                    <x-lockups.feed-message-lockup :feed-message="$feedMessage" />
                @endforeach
            </div>

            <div class="mt-4">
                {{ $this->feedMessages->links() }}
            </div>
        </section>
    @endif
</div>
