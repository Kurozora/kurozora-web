<?php

namespace App\Notifications;

use App\Models\User;
use App\Notifications\Concerns\BroadcastsAsNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;

class NewEpisode extends Notification implements ShouldQueue
{
    use BroadcastsAsNotification,
        Queueable;

    /**
     * The public id of the episode that aired.
     *
     * @var string $episodePublicID
     */
    private string $episodePublicID;

    /**
     * The number of the episode that aired.
     *
     * @var int $episodeNumber
     */
    private int $episodeNumber;

    /**
     * The slug of the anime the episode belongs to.
     *
     * @var string $animeSlug
     */
    private string $animeSlug;

    /**
     * The anime's title per locale, keyed by locale code.
     *
     * @var array $animeTitles
     */
    private array $animeTitles;

    /**
     * The anime's title to fall back to when no localized title exists.
     *
     * @var string $fallbackTitle
     */
    private string $fallbackTitle;

    /**
     * The poster image url of the anime the episode belongs to.
     *
     * @var string|null $posterImageURL
     */
    private ?string $posterImageURL;

    /**
     * Create a new notification instance.
     *
     * @param string      $episodePublicID
     * @param int         $episodeNumber
     * @param string      $animeSlug
     * @param array       $animeTitles
     * @param string      $fallbackTitle
     * @param string|null $posterImageURL
     */
    public function __construct(string $episodePublicID, int $episodeNumber, string $animeSlug, array $animeTitles, string $fallbackTitle, ?string $posterImageURL)
    {
        $this->episodePublicID = $episodePublicID;
        $this->episodeNumber = $episodeNumber;
        $this->animeSlug = $animeSlug;
        $this->animeTitles = $animeTitles;
        $this->fallbackTitle = $fallbackTitle;
        $this->posterImageURL = $posterImageURL;
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
        return ['database', 'broadcast', ApnChannel::class];
    }

    /**
     * Get the database representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toDatabase(mixed $notifiable): array
    {
        return $this->payload();
    }

    /**
     * Get the broadcast representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return BroadcastMessage
     */
    public function toBroadcast(mixed $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload());
    }

    /**
     * Get the APN representation of the notification.
     *
     * @param User $notifiable
     *
     * @return ApnMessage
     */
    public function toApn(User $notifiable): ApnMessage
    {
        return ApnMessage::create()
            ->title(__('New episode'))
            ->badge($notifiable->unreadNotifications()->count())
            ->body(__('Episode :number of :title is out now.', [
                'number' => $this->episodeNumber,
                'title' => $this->resolvedTitle(),
            ]))
            ->custom('notification_id', $this->id);
    }

    /**
     * The representation shared by the database and broadcast channels.
     *
     * @return array
     */
    private function payload(): array
    {
        return [
            'episodeID' => $this->episodePublicID,
            'number' => $this->episodeNumber,
            'animeSlug' => $this->animeSlug,
            'animeTitle' => $this->resolvedTitle(),
            'posterImageURL' => $this->posterImageURL,
        ];
    }

    /**
     * The anime title in the recipient's locale, falling back as needed.
     *
     * @return string
     */
    private function resolvedTitle(): string
    {
        return $this->animeTitles[app()->getLocale()]
            ?? $this->animeTitles[config('app.fallback_locale')]
            ?? $this->fallbackTitle;
    }
}
