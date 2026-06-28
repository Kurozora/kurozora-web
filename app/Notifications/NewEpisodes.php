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

class NewEpisodes extends Notification implements ShouldQueue
{
    use BroadcastsAsNotification,
        Queueable;

    /**
     * The slug of the anime the episodes belong to.
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
     * The poster image url of the anime the episodes belong to.
     *
     * @var string|null $posterImageURL
     */
    private ?string $posterImageURL;

    /**
     * The aired episodes, each as an `episodeID` and `number` pair.
     *
     * @var array $episodes
     */
    private array $episodes;

    /**
     * Create a new notification instance.
     *
     * @param string      $animeSlug
     * @param array       $animeTitles
     * @param string      $fallbackTitle
     * @param string|null $posterImageURL
     * @param array       $episodes
     */
    public function __construct(string $animeSlug, array $animeTitles, string $fallbackTitle, ?string $posterImageURL, array $episodes)
    {
        $this->animeSlug = $animeSlug;
        $this->animeTitles = $animeTitles;
        $this->fallbackTitle = $fallbackTitle;
        $this->posterImageURL = $posterImageURL;
        $this->episodes = $episodes;
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
            ->title(__('New episodes'))
            ->badge($notifiable->unreadNotifications()->count())
            ->body(__(':count new episodes of :title are out now.', [
                'count' => count($this->episodes),
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
            'animeSlug' => $this->animeSlug,
            'animeTitle' => $this->resolvedTitle(),
            'posterImageURL' => $this->posterImageURL,
            'episodes' => $this->episodes,
            'count' => count($this->episodes),
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
