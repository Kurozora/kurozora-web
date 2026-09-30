<x-base-layout>
    <x-slot:title>
        {{ __('Active Sessions') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Manage and sign out your active sessions on other devices.') }}
    </x-slot:description>

    <x-slot:meta>
        <meta name="robots" content="noindex" />
        <link rel="canonical" href="{{ route('profile.settings.sessions') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        profile/settings/sessions
    </x-slot:appArgument>

    <x-slot:scripts>
        @vite(['resources/js/sessions-map.js'])
    </x-slot:scripts>

    <main>
        @if (filled($mapToken))
            <section>
                <div id="sessions-map" class="w-full h-64 overflow-hidden bg-secondary" data-sessions-map data-token="{{ $mapToken }}" data-coordinates="{{ json_encode($coordinates) }}"></div>
            </section>
        @endif

        <div
            class="pt-4 pb-6"
            x-data="sessionsPage({{ Js::from([
                'labels' => [
                    'selected' => __(':count Selected'),
                    'select' => __('Select Sessions'),
                ],
            ]) }})"
            x-on:sessions-signed-out.window="settle()"
            x-on:sessions-sign-out-failed.window="fail($event.detail)"
            x-on:user-actions-failed.window="busy = false"
        >
            <section class="mb-4 xl:safe-area-inset">
                <div class="flex flex-wrap items-baseline justify-between gap-2 pl-4 pr-4">
                    <div class="flex flex-col">
                        <h1 class="text-2xl font-bold">{{ __('Active Sessions') }}</h1>

                        <p
                            class="text-sm text-secondary leading-5"
                            style="min-height: 1.25rem"
                            x-text="selectMode ? countLabel() : ''"
                        ></p>
                    </div>

                    <div class="flex items-center gap-4">
                        <template x-if="!selectMode">
                            <div class="flex items-center gap-4">
                                <button
                                    type="button"
                                    class="text-sm text-tint hover:opacity-75 cursor-pointer"
                                    x-on:click="enterSelectMode()"
                                >
                                    {{ __('Select') }}
                                </button>

                                <button
                                    type="button"
                                    class="text-sm text-red-500 hover:opacity-75 cursor-pointer"
                                    x-on:click="confirm([], true)"
                                >
                                    {{ __('Sign Out All') }}
                                </button>
                            </div>
                        </template>

                        <template x-if="selectMode">
                            <div class="flex items-center gap-4">
                                <button
                                    type="button"
                                    class="text-sm text-tint hover:opacity-75 cursor-pointer"
                                    x-on:click="toggleSelectAll()"
                                    x-text="allSelected ? @js(__('Deselect All')) : @js(__('Select All'))"
                                ></button>

                                <button
                                    type="button"
                                    class="text-sm text-red-500 hover:opacity-75 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                                    x-bind:disabled="!hasSelection"
                                    x-on:click="batchSignOut()"
                                >
                                    {{ str(__('Sign out'))->title() }}
                                </button>

                                <button
                                    type="button"
                                    class="text-sm text-secondary hover:text-primary cursor-pointer"
                                    x-on:click="exitSelectMode()"
                                    aria-label="{{ __('Cancel') }}"
                                    title="{{ __('Cancel') }}"
                                >
                                    @svg('xmark', 'fill-current', ['width' => 14])
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </section>

            <div data-paginated="sessions" data-paginated-refresh-on="sessions-signed-out">
                @if ($currentSession !== null)
                    <section class="mb-6 xl:safe-area-inset">
                        <h2 class="text-sm font-semibold text-secondary mb-2 pl-4 pr-4">{{ __('Current Session') }}</h2>

                        <div class="bg-secondary rounded-xl pl-2 pr-2 ml-4 mr-4">
                            <x-lockups.session-lockup :session="$currentSession" />
                        </div>
                    </section>
                @endif

                @if ($otherSessions->isNotEmpty())
                    <section class="xl:safe-area-inset">
                        <h2 class="text-sm font-semibold text-secondary mb-2 pl-4 pr-4">{{ __('Other Sessions') }}</h2>

                        <div class="bg-secondary rounded-xl pl-2 pr-2 ml-4 mr-4">
                            <ul class="flex flex-col m-0">
                                @foreach ($otherSessions as $key => $session)
                                    <li
                                        class="relative group rounded-md"
                                        key="session-{{ $session->key }}"
                                        data-session-key="{{ $session->key }}"
                                    >
                                        <x-lockups.session-lockup :session="$session" :supports-select="true" />

                                        <div
                                            class="absolute top-1 right-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity duration-150"
                                            x-show="!selectMode"
                                        >
                                            <div class="flex items-center gap-3 pl-2 pr-2 pt-1 pb-1 bg-secondary border border-primary rounded-md shadow-sm">
                                                <button
                                                    type="button"
                                                    class="text-xs text-red-500 hover:opacity-75 cursor-pointer"
                                                    x-on:click="confirm([{{ Js::from($session->key) }}])"
                                                >
                                                    {{ str(__('Sign out'))->title() }}
                                                </button>
                                            </div>
                                        </div>

                                        @if ($key !== $otherSessions->count() - 1)
                                            <x-hr class="m-0" />
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </section>
                @endif
            </div>

            <!-- Sign Out Confirmation Modal -->
            <x-dialog-modal id="sessions-sign-out">
                <x-slot:title>
                    {{ str(__('Sign out'))->title() }}
                </x-slot:title>

                <x-slot:content>
                    <div class="pt-4 pb-4 pl-4 pr-4">
                        <p>{{ __('Please enter your password to confirm you would like to sign out of the selected sessions.') }}</p>

                        <div class="mt-4">
                            <x-input type="password" class="mt-1 block w-3/4" placeholder="{{ __('Password') }}"
                                     x-model="password"
                                     x-on:keydown.enter="signOut()" />

                            <p class="mt-2 text-sm text-red-600" x-show="error" x-text="error" x-cloak></p>
                        </div>
                    </div>
                </x-slot:content>

                <x-slot:footer>
                    <x-outlined-button x-on:click="close()" x-bind:disabled="busy">
                        {{ __('Nevermind') }}
                    </x-outlined-button>

                    <x-button class="ml-2" x-on:click="signOut()" x-bind:disabled="busy">
                        {{ str(__('Sign out'))->title() }}
                    </x-button>
                </x-slot:footer>
            </x-dialog-modal>
        </div>
    </main>
</x-base-layout>
