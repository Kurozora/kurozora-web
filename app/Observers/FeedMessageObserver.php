<?php

namespace App\Observers;

use App\Models\FeedMessage;

class FeedMessageObserver
{
    /**
     * Handle the FeedMessage "created" event.
     *
     * @param FeedMessage $feedMessage
     *
     * @return void
     */
    public function created(FeedMessage $feedMessage): void
    {
        $column = $this->parentCounterColumn($feedMessage);

        if ($column === null || $feedMessage->parent_feed_message_id === null) {
            return;
        }

        FeedMessage::whereKey($feedMessage->parent_feed_message_id)
            ->increment($column);
    }

    /**
     * Handle the FeedMessage "deleted" event.
     *
     * @param FeedMessage $feedMessage
     *
     * @return void
     */
    public function deleted(FeedMessage $feedMessage): void
    {
        $column = $this->parentCounterColumn($feedMessage);

        if ($column === null || $feedMessage->parent_feed_message_id === null) {
            return;
        }

        FeedMessage::whereKey($feedMessage->parent_feed_message_id)
            ->where($column, '>', 0)
            ->decrement($column);
    }

    /**
     * Returns the counter column on the parent message that this message contributes to.
     *
     * @param FeedMessage $feedMessage
     *
     * @return string|null
     */
    private function parentCounterColumn(FeedMessage $feedMessage): ?string
    {
        if ($feedMessage->is_reply) {
            return 'replies_count';
        }

        if ($feedMessage->is_reshare) {
            return 're_shares_count';
        }

        return null;
    }
}
