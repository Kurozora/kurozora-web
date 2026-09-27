<div>
    <x-select-button rounded="full" chevronClass="w-4 h-4 btn-text-tinted sm:w-6 sm:h-6" class="w-24 pl-3 pr-5 pt-2 pb-2 bg-tint text-xs btn-text-tinted font-semibold border-0 shadow-md hover:bg-tint-800 active:bg-tint focus:ring-0 sm:w-32 sm:pl-3 sm:pr-7" wire:model.live="libraryStatus" wire:change="updateLibraryStatus">
        <option value="-1" selected hidden disabled>{{ str(__('Add'))->upper() }}</option>

        @foreach ($this->userLibraryStatus as $key => $userLibraryStatus)
            <option value="{{ $key }}">{{ __($userLibraryStatus) }}</option>
        @endforeach

        @if ($libraryStatus != -1)
            <option class="text-red-500" value="-2">{{ __('Remove from Library') }}</option>
        @endif
    </x-select-button>
</div>
