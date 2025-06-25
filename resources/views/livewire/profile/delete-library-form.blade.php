<x-action-section>
    <x-slot:title>
        {{ __('Delete Library') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Permanently delete your library.') }}
    </x-slot:description>

    <x-slot:content>
        <div class="max-w-xl text-sm text-primary">
            {{ __('Once your library is deleted, all of its resources and data will be permanently deleted. This includes ratings, favorites, reminders, watched episodes, and You will be asked for your password to confirm the deletion.') }}
        </div>

        <div class="mt-5">
            <x-select id="library" wire:model="state.library">
                <option value="-1">{{ __('Select library') }}</option>
                @foreach (App\Enums\UserLibraryKind::asSelectArray() as $value => $libraryKind)
                    <option value="{{ $value }}">{{ $libraryKind }}</option>
                @endforeach
            </x-select>

            <x-input-error for="library" class="mt-2"/>
        </div>

        <div class="flex gap-2 items-center mt-5">
            <x-danger-button wire:click="confirmLibraryDeletion" wire:loading.attr="disabled">
                {{ __('Delete Library') }}
            </x-danger-button>

            <x-action-message class="mr-3" on="deleted">
                @if ($this->library)
                    {{ __(':x library deleted.', ['x' => $this->library->key]) }}
                @else
                    {{ __('Library deleted.') }}
                @endif
            </x-action-message>
        </div>

        <!-- Delete Library Confirmation Modal -->
        <x-dialog-modal model="confirmingLibraryDeletion">
            <x-slot:title>
                {{ __('Delete Library') }}
            </x-slot:title>

            <x-slot:content>
                <div class="pt-4 pb-4 pl-4 pr-4">
                    <p>{{ __('Are you sure you want to delete your library? Once your library is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your library.') }}</p>

                    <div class="mt-4" x-data="{}" x-on:confirming-delete-library.window="setTimeout(() => $refs.password.focus(), 250)">
                        <x-input type="password" class="mt-1 block w-3/4" placeholder="{{ __('Password') }}"
                                 x-ref="password"
                                 wire:model="password"
                                 wire:keydown.enter="deleteLibrary" />

                        <x-input-error for="password" class="mt-2" />
                    </div>
                </div>
            </x-slot:content>

            <x-slot:footer>
                <x-outlined-button wire:click="$toggle('confirmingLibraryDeletion')" wire:loading.attr="disabled">
                    {{ __('Nevermind') }}
                </x-outlined-button>

                <x-danger-button class="ml-2" wire:click="deleteLibrary" wire:loading.attr="disabled">
                    {{ __('Delete Library') }}
                </x-danger-button>
            </x-slot:footer>
        </x-dialog-modal>
    </x-slot:content>
</x-action-section>
