<span>
    <x-square-button wire:click="$toggle('showHelp')">
        @svg('questionmark', 'fill-current', ['width' => 20])
    </x-square-button>

    <x-dialog-modal maxWidth="md" model="showHelp">
        <x-slot:title>
            {{ __('Search Tips') }}
        </x-slot:title>

        <x-slot:description>
            {{ __('Unleash your inner ninja and master the art of finding your favorites, no Sharingan required!') }}
        </x-slot:description>

        <x-slot:content>
            <ul class="flex flex-wrap justify-between m-0 mb-4 gap-6 list-none">
                <li class="flex flex-col">
                    <p class="font-bold uppercase">{{ __('Quick Search') }}</p>
                    <p class="text-sm">{{ __('Invoke global search without leaving your keyboard.') }}</p>
                    <div class="flex gap-2 pt-1 pb-1">
                        <p class="pr-1 pl-1 bg-gray-100 font-semibold rounded">{{ __('⌘+K') }}</p>
                        <p>{{ __('or') }}</p>
                        <p class="pr-1 pl-1 bg-gray-100 font-semibold rounded">{{ __('ctrl+K') }}</p>
                        <p>{{ __('or') }}</p>
                        <p class="pr-1 pl-1 bg-gray-100 font-semibold rounded">{{ __('/') }}</p>
                    </div>
                </li>

                <li class="flex flex-col">
                    <span class="flex gap-2">
                        <p class="font-bold uppercase">{{ __('Exact Match') }}</p>
                    </span>
                    <p class="text-sm">{{ __('Enclose one or more words in double quotes.') }}</p>
                    <div class="flex gap-2 pt-1 pb-1">
                        <p class="pr-1 pl-1 bg-gray-100 font-semibold rounded">{{ __('"Hunter x Hunter"') }}</p>
                    </div>
                </li>

                <li class="flex flex-col">
                    <span class="flex gap-2">
                        <p class="font-bold uppercase">{{ __('Exclude Words') }}</p>
                        <p class="pr-1 pl-1 bg-green-500 text-white font-semibold rounded">{{ __('New') }}</p>
                    </span>
                    <p class="text-sm">{{ __('Append a minus in front of a word you want to leave out.') }}</p>
                    <div class="flex gap-2 pt-1 pb-1">
                        <p class="pr-1 pl-1 bg-gray-100 font-semibold rounded">{{ __('-death note') }}</p>
                    </div>
                </li>
            </ul>
        </x-slot:content>

        <x-slot:footer>
            <x-button wire:click="$toggle('showHelp')">{{ __('Ok') }}</x-button>
        </x-slot:footer>
    </x-dialog-modal>
</span>
