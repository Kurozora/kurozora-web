<?php

namespace App\Livewire\Notifications;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    /**
     * Register listeners for the component.
     *
     * @return array
     */
    public function getListeners(): array
    {
        if (!auth()->check()) {
            return [];
        }

        return [
            'echo-notification:App.Models.User.' . auth()->id() => 'onNotificationReceived',
        ];
    }

    /**
     * Refresh notification state when a new push arrives.
     *
     * @return void
     */
    public function onNotificationReceived(): void
    {
        $this->resetPage();
        unset($this->notifications);
    }

    /**
     * Returns the paginated list of the user's notifications.
     *
     * @return LengthAwarePaginator
     */
    #[Computed]
    public function notifications(): LengthAwarePaginator
    {
        return auth()->user()
            ->notifications()
            ->with(['notifier'])
            ->latest()
            ->paginate(25);
    }

    /**
     * Sets the read status for the given notification IDs.
     *
     * @param array $ids
     * @param bool  $read
     *
     * @return void
     */
    public function setReadStatus(array $ids, bool $read): void
    {
        if (empty($ids)) {
            return;
        }

        DB::transaction(function () use ($ids, $read) {
            $query = auth()->user()
                ->notifications()
                ->whereIn('id', $ids);

            if ($read) {
                $query->whereNull('read_at')->update(['read_at' => now()]);
            } else {
                $query->whereNotNull('read_at')->update(['read_at' => null]);
            }
        });

        unset($this->notifications);
    }

    /**
     * Deletes the given notification IDs.
     *
     * @param array $ids
     *
     * @return void
     */
    public function deleteMany(array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        DB::transaction(function () use ($ids) {
            auth()->user()
                ->notifications()
                ->whereIn('id', $ids)
                ->delete();
        });

        unset($this->notifications);
    }

    /**
     * Marks a single notification as read.
     *
     * @param string $id
     *
     * @return void
     */
    public function markRead(string $id): void
    {
        $this->setReadStatus([$id], true);
    }

    /**
     * Marks a single notification as unread.
     *
     * @param string $id
     *
     * @return void
     */
    public function markUnread(string $id): void
    {
        $this->setReadStatus([$id], false);
    }

    /**
     * Deletes a single notification.
     *
     * @param string $id
     *
     * @return void
     */
    public function deleteSingle(string $id): void
    {
        $this->deleteMany([$id]);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.notifications.index');
    }
}
