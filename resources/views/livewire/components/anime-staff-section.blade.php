<div>
    @if ($staffCount)
        <section class="pt-5 pb-8 pl-4 pr-4 border-t-2">
            <x-section-nav>
                <x-slot:title>
                    {{ __('Staff') }}
                </x-slot:title>

                <x-slot:action>
                    @hasrole('superAdmin')
                    <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
                    @endhasrole
                    <x-section-nav-link href="{{ route('anime.staff', $anime) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot:action>
            </x-section-nav>

            <div class="flex justify-center">
                <x-spinner />
            </div>

            <div class="grid grid-flow-col-dense gap-4 justify-start overflow-x-scroll no-scrollbar">
                @foreach($this->staff as $animeStaff)
                    <x-lockups.staff-lockup :staff="$animeStaff" />
                @endforeach
            </div>
        </section>
    @endif
</div>
