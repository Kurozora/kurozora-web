<?php

namespace App\Models;

use App\Enums\ScheduledNotificationStatus;
use App\Enums\ScheduledNotificationType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ScheduledNotification extends KModel
{
    use MassPrunable;

    // Table name
    const string TABLE_NAME = 'scheduled_notifications';
    protected $table = self::TABLE_NAME;

    /**
     * The attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'type' => ScheduledNotificationType::class,
            'status' => ScheduledNotificationStatus::class,
            'scheduled_at' => 'datetime',
            'dispatched_at' => 'datetime',
        ];
    }

    /**
     * The model the notification is about.
     *
     * @return MorphTo
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The query that selects the notifications eligible for pruning.
     *
     * @return Builder
     */
    public function prunable(): Builder
    {
        return static::where('status', '!=', ScheduledNotificationStatus::Pending)
            ->where('updated_at', '<', now()->subDay());
    }
}
