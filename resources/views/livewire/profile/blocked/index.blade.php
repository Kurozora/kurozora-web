@php
    $blockedAccountsDescription = __('When you block someone, they will be able to see your public messages, but will no longer be able to engage with them. They will also not be able to follow or message you, and you will not see notifications from them.');
@endphp

<main>
    <x-slot:title>
        {{ __('Blocked Accounts') }}
    </x-slot:title>

    <x-slot:description>
        {{ $blockedAccountsDescription }}
    </x-slot:description>

    <x-slot:meta>
        <meta name="robots" content="noindex" />
    </x-slot:meta>

    <x-slot:appArgument>
        users/{{ $user->id }}/blocked
    </x-slot:appArgument>

    <div class="pt-4 pb-6" wire:init="loadPage">
        <section class="mb-4 xl:safe-area-inset">
            <div>
                <div class="flex flex-col gap-2 pl-4 pr-4">
                    <h1 class="text-2xl font-bold">{{ __('Blocked Accounts') }}</h1>
                    <p class="text-sm text-secondary">{{ $blockedAccountsDescription }}</p>
                </div>
            </div>
        </section>

        @if ($this->blockedUsers->count())
            <section class="xl:safe-area-inset">
                <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
                    @foreach ($this->blockedUsers as $blockedUser)
                        <x-lockups.user-lockup :user="$blockedUser" :is-row="false" wire:key="blocked-{{ $blockedUser->id }}">
                            <x-slot:trailingAction>
                                <x-button
                                    class="!bg-red-500 hover:!bg-red-600"
                                    wire:click="unblock({{ $blockedUser->id }})"
                                    wire:loading.attr="disabled"
                                >
                                    {{ __('Blocked') }}
                                </x-button>
                            </x-slot:trailingAction>
                        </x-lockups.user-lockup>
                    @endforeach

                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                </div>

                <div class="mt-4 pl-4 pr-4">
                    {{ $this->blockedUsers->links() }}
                </div>
            </section>
        @elseif (!$readyToLoad)
            <section class="mt-4 xl:safe-area-inset">
                <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
                    @foreach (range(1, 10) as $range)
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 96px;"></div>
                    @endforeach
                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                </div>
            </section>
        @else
            <section class="flex flex-col items-center justify-center mt-4 text-center xl:safe-area-inset" style="min-height: 50vh;">
                <p class="font-bold">{{ __('No Blocked Accounts') }}</p>
                <p class="text-sm text-secondary">{{ __('You haven’t blocked anyone yet.') }}</p>
            </section>
        @endif
    </div>
</main>
