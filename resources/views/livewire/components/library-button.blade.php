<div>
    <x-select-button rounded="full" chevronClass="w-4 h-4 text-white sm:w-6 sm:h-6" class="w-24 pl-3 pr-5 pt-2 pb-2 bg-orange-500 text-xs text-white font-semibold border-0 shadow-md hover:bg-orange-400 active:bg-orange-600 focus:ring-0 sm:w-32 sm:pl-3 sm:pr-7" wire:model="libraryStatus" wire:change="updateLibraryStatus">
        <option value="-1" selected hidden disabled>{{ __('ADD') }}</option>

        @foreach($this->userLibraryStatus as $key => $userLibraryStatus)
            <option value="{{ $key }}">{{ __($userLibraryStatus) }}</option>
        @endforeach

        @if ($libraryStatus != -1)
            <option class="text-red-500" value="-2">{{ __('Remove from Library') }}</option>
        @endif
    </x-select-button>
</div>