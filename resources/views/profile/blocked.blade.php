@php
    $blockedAccountsDescription = __('When you block someone, they will be able to see your public messages, but will no longer be able to engage with them. They will also not be able to follow or message you, and you will not see notifications from them.');
@endphp

<x-base-layout>
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

    <main>
        <div class="pt-4 pb-6">
            <section class="mb-4 xl:safe-area-inset">
                <div>
                    <div class="flex flex-col gap-2 pl-4 pr-4">
                        <h1 class="text-2xl font-bold">{{ __('Blocked Accounts') }}</h1>
                        <p class="text-sm text-secondary">{{ $blockedAccountsDescription }}</p>
                    </div>
                </div>
            </section>

            @if ($blockedUsers->count())
                <section class="xl:safe-area-inset">
                    <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
                        @foreach ($blockedUsers as $blockedUser)
                            <div
                                class="contents"
                                x-data="{ blocked: true, busy: false }"
                                x-on:user-blocked.window="if ($event.detail.id === {{ $blockedUser->id }}) { blocked = $event.detail.blocked; busy = false }"
                                x-on:user-actions-failed.window="busy = false"
                            >
                                <x-lockups.user-lockup :user="$blockedUser" :is-row="false" x-show="blocked">
                                    <x-slot:trailingAction>
                                        <x-button
                                            class="!bg-red-500 hover:!bg-red-600"
                                            x-on:click="busy = true; Livewire.dispatch('user-block', { id: {{ $blockedUser->id }} })"
                                            x-bind:disabled="busy"
                                        >
                                            {{ __('Blocked') }}
                                        </x-button>
                                    </x-slot:trailingAction>
                                </x-lockups.user-lockup>
                            </div>
                        @endforeach

                        <div class="w-64 md:w-80 flex-grow"></div>
                        <div class="w-64 md:w-80 flex-grow"></div>
                    </div>

                    <div class="mt-4 pl-4 pr-4">
                        {{ $blockedUsers->links() }}
                    </div>
                </section>
            @else
                <x-empty-state :heading="__('No Blocked Accounts')" :description="__('You haven’t blocked anyone yet.')" />
            @endif
        </div>
    </main>
</x-base-layout>
