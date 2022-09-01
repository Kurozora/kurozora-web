<div
    class="relative flex flex-col h-full w-full"
    x-data="{
        selectedChatOption: $persist('selectedChatOption')
    }"
>
    {{-- Header --}}
    <div class="w-full bg-gray-50">
        <div class="flex flex-row justify-center gap-4 p-2 w-full">
            @foreach([__('Live Chat'), __('Top Chat')] as $chatOption)
                <template x-if="selectedChatOption === '{{ $chatOption }}'">
                    <x-button>{{ $chatOption }}</x-button>
                </template>

                <template x-if="selectedChatOption !== '{{ $chatOption }}'">
                    <x-outlined-button x-on:click="selectedChatOption = '{{ $chatOption }}'">{{ $chatOption }}</x-outlined-button>
                </template>
            @endforeach
        </div>

        <x-hr />
    </div>

    {{-- Body --}}
    <div class="flex flex-col gap-2 p-2 h-full overflow-scroll z-10">
        @foreach(range(1, 20) as $key)
            <div class="flex flex-row">
                <div class="flex">
                    <picture class="relative w-full overflow-hidden">
                        <img class="bg-white border-2 border-black/5 rounded-full aspect-square" src="{{ asset('images/static/placeholders/user_profile.webp') }}" alt="{{ __('Guest') }} Profile Image" width="44" height="44">

                        <div class="absolute top-0 left-0 h-full w-full"></div>
                    </picture>
                </div>

                <p class="ml-1 text-sm text-gray-500">23:45pm</p>
                <p class="flex-1 ml-2">A random comment jsahdk jahsd jkahsdk jahks djhak sdjhakj sdhkaj sdhka jshdkja dhsjadsjahsj h</p>
            </div>
        @endforeach
    </div>

    {{-- Footer --}}
    <div class="bg-gray-50 z-10">
        <x-hr />

        <div
            class="flex flex-row p-2"
            x-data="{
                comment: $persist(@entangle('comment')).as('_x_comment' + window.location.pathname.replaceAll('/', '_')),
            }"
        >
            <x-textarea
                id="commentBox"
                class="bg-transparent resize-none"
                placeholder="{{ __('Add a comment...') }}"
                rows="1"
                x-model.debounce.1000ms="comment"
                rounded="full"
                :autoresize="true"
            ></x-textarea>

            <div class="flex">
                <x-emoji />

                <button
                    class="flex justify-center text-orange-500"
                    style="width: 44px; height: 44px;"
                    type="submit"
                >
                    @svg('arrow_up_circle_fill', 'fill-current', ['width' => 24])
                </button>
            </div>
        </div>
    </div>
</div>
