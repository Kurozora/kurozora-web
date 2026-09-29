<div
    x-data="feedMessageModals()"
    x-on:feed-message-modal.window="open($event.detail)"
    x-on:feed-message-edited.window="settle($event.detail)"
    x-on:feed-message-deleted.window="settle($event.detail)"
    x-on:feed-message-reshared.window="settle($event.detail)"
    x-on:user-actions-failed.window="busy = false"
>
    <x-dialog-modal id="feed-message-edit" maxWidth="md">
        <x-slot:title>
            {{ __('Edit Message') }}
        </x-slot:title>

        <x-slot:content>
            <div class="pt-4 pb-4 pl-4 pr-4">
                <x-textarea class="w-full" rows="4" x-model="content" />
            </div>
        </x-slot:content>

        <x-slot:footer>
            <div class="flex justify-end gap-2">
                <x-outlined-button x-on:click="close()" x-bind:disabled="busy">
                    {{ __('Cancel') }}
                </x-outlined-button>

                <x-button x-on:click="confirmEdit()" x-bind:disabled="busy">
                    {{ __('Save') }}
                </x-button>
            </div>
        </x-slot:footer>
    </x-dialog-modal>

    <x-dialog-modal id="feed-message-delete" maxWidth="md">
        <x-slot:title>
            {{ __('Delete Message') }}
        </x-slot:title>

        <x-slot:content>
            <div class="pt-4 pb-4 pl-4 pr-4">
                <p>{{ __('Are you sure you want to delete this message?') }}</p>
            </div>
        </x-slot:content>

        <x-slot:footer>
            <div class="flex justify-end gap-2">
                <x-outlined-button x-on:click="close()" x-bind:disabled="busy">
                    {{ __('Cancel') }}
                </x-outlined-button>

                <x-button x-on:click="confirmDelete()" x-bind:disabled="busy">
                    {{ __('Delete') }}
                </x-button>
            </div>
        </x-slot:footer>
    </x-dialog-modal>

    <x-dialog-modal id="feed-message-quote" maxWidth="md">
        <x-slot:title>
            {{ __('Quote Message') }}
        </x-slot:title>

        <x-slot:content>
            <div class="pt-4 pb-4 pl-4 pr-4">
                <x-textarea class="w-full" rows="4" placeholder="{{ __('Add a comment') }}" x-model="content" />
            </div>
        </x-slot:content>

        <x-slot:footer>
            <div class="flex justify-end gap-2">
                <x-outlined-button x-on:click="close()" x-bind:disabled="busy">
                    {{ __('Cancel') }}
                </x-outlined-button>

                <x-button x-on:click="confirmQuote()" x-bind:disabled="busy">
                    {{ __('Quote') }}
                </x-button>
            </div>
        </x-slot:footer>
    </x-dialog-modal>

    <x-dialog-modal id="feed-message-report" maxWidth="md">
        <x-slot:title>
            {{ __('Report Message') }}
        </x-slot:title>

        <x-slot:content>
            <div class="pt-4 pb-4 pl-4 pr-4">
                <p>{{ __('Are you sure you want to report this message?') }}</p>
            </div>
        </x-slot:content>

        <x-slot:footer>
            <div class="flex justify-end gap-2">
                <x-outlined-button x-on:click="close()">
                    {{ __('Cancel') }}
                </x-outlined-button>

                <x-button x-on:click="close()">
                    {{ __('Report') }}
                </x-button>
            </div>
        </x-slot:footer>
    </x-dialog-modal>

    <x-dialog-modal id="feed-message-share" maxWidth="md">
        <x-slot:title>
            {{ __('Share Message') }}
        </x-slot:title>

        <x-slot:content>
            <div class="pt-4 pb-4 pl-4 pr-4">
                <p>{{ __('Share this message with others?') }}</p>
            </div>
        </x-slot:content>

        <x-slot:footer>
            <div class="flex justify-end gap-2">
                <x-outlined-button x-on:click="close()">
                    {{ __('Cancel') }}
                </x-outlined-button>

                <x-button x-on:click="close()">
                    {{ __('Share') }}
                </x-button>
            </div>
        </x-slot:footer>
    </x-dialog-modal>
</div>
