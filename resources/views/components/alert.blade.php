@props(['message'])

<div
    x-data="{
        openAlertBox: true
    }"
    x-init="setTimeout(function () { openAlertBox = false }, 2500)"
>
    <div
        class="fixed top-0 right-0 py-16 pl-4 pr-4 z-[999]"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-show="openAlertBox"
    >
        <div class="flex items-center bg-green-500 text-white text-sm font-bold pl-4 pr-4 py-3 rounded shadow-md" role="alert">
            <svg fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5 mr-2 text-white"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="flex">{{ $message }}</span>
            <button type="button" class="flex" @click="openAlertBox = false">
                <svg fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4 ml-4">
                    <path d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
</div>
