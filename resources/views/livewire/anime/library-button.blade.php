<div
    x-data="{
        libraryStatus: $wire.entangle('libraryStatus'),
    }"
>
    <x-select-button class="w-auto max-w-[150px] bg-orange-500 text-white font-semibold border-0 rounded-full shadow-md hover:bg-orange-400 active:bg-orange-600 focus:ring-0" x-model="libraryStatus" wire:model="libraryStatus" wire:change="updateLibraryStatus">
        <option value="-1" selected hidden disabled>{{ __('ADD') }}</option>

        @foreach(App\Enums\UserLibraryStatus::asSelectArray() as $key => $userLibraryStatus)
            <option value="{{ $key }}">{{ __($userLibraryStatus) }}</option>
        @endforeach

        @if($libraryStatus != -1)
            <option class="text-red-500" value="-2" x-show="libraryStatus != -1" wire:click="$set('libraryStatus', -1)">{{ __('Remove from Library') }}</option>
        @endif
    </x-select-button>
</div>
