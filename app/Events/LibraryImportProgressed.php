<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LibraryImportProgressed implements ShouldBroadcastNow
{
    use Dispatchable,
        InteractsWithSockets,
        SerializesModels;

    /**
     * The identifier of the user whose library is being imported.
     *
     * @var int $userID
     */
    public int $userID;

    /**
     * The number of entries the import has processed so far.
     *
     * @var int $processedCount
     */
    public int $processedCount;

    /**
     * The number of entries the import will process in total.
     *
     * @var int $totalCount
     */
    public int $totalCount;

    /**
     * Create a new event instance.
     *
     * @param int $userID
     * @param int $processedCount
     * @param int $totalCount
     */
    public function __construct(int $userID, int $processedCount, int $totalCount)
    {
        $this->userID = $userID;
        $this->processedCount = $processedCount;
        $this->totalCount = $totalCount;
    }

    /**
     * The private channel this event is broadcast on.
     *
     * @return PrivateChannel
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('users.' . $this->userID);
    }

    /**
     * The Pusher-protocol event name advertised on the wire.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'library.import.progressed';
    }

    /**
     * The payload sent to subscribers.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'processedCount' => $this->processedCount,
            'totalCount' => $this->totalCount,
        ];
    }
}
