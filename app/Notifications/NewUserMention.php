<?php
namespace App\Notifications;

use App\Models\Comment;
use App\Models\FeedMessage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;

class NewUserMention extends Notification
{
    use Queueable;

    /**
     * The model instance.
     *
     * @var Comment|FeedMessage $model
     */
    public Comment|FeedMessage $model;

    /**
     * Create a new notification instance.
     *
     * @param Comment|FeedMessage $model
     */
    public function __construct(Comment|FeedMessage $model)
    {
        $this->model = $model;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via(mixed $notifiable): array
    {
        return ['database', ApnChannel::class];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toDatabase(mixed $notifiable): array
    {
        $modelID = $this->model->getKey();

        if ($this->model instanceof Comment) {
            $link = route('comment', $modelID);
        } else {
            $link = route('api.feed.messages.details', $modelID);
        }

        return [
            'title' => $this->model->user->username . ' mentioned you',
            'message' => $this->model->content,
            'link' => $link,
            'type' => 'mention'
        ];
    }

    /**
     * Get the APN representation of the notification.
     *
     * @param User $notifiable
     * @return ApnMessage
     */
    public function toApn(User $notifiable): ApnMessage
    {
        return ApnMessage::create()
            ->title($this->model->user->username . ' mentioned you')
            ->badge($notifiable->unreadNotifications()->count())
            ->body($this->model->content);
    }
}
