<?php

namespace App\Models;

use App\Notifications\LibraryImportFinished;
use App\Notifications\LibraryImportUnsupported;
use App\Notifications\LocalLibraryImportFinished;
use App\Notifications\NewFeedMessageReply;
use App\Notifications\NewFeedMessageReShare;
use App\Notifications\NewFollower;
use App\Notifications\NewSession;
use App\Notifications\NewUserMention;
use App\Notifications\SubscriptionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\DatabaseNotification;
use Staudenmeir\EloquentJsonRelations\HasJsonRelationships;

class Notification extends DatabaseNotification
{
    use HasJsonRelationships,
        MassPrunable;

    // Table name
    const string TABLE_NAME = 'notifications';
    protected $table = self::TABLE_NAME;

    /**
     * Get the prunable model query.
     *
     * @return Builder
     */
    public function prunable(): Builder
    {
        return static::whereNotNull('read_at')
            ->where('read_at', '<=', now()->subWeeks(2));
    }

    /**
     * Returns the body string that describes the notification.
     *
     * @return string
     */
    public function getDescriptionAttribute(): string
    {
        switch ($this->type) {
            // Session notifications
            case NewSession::class:
                $body = 'A new client has logged in to your account.';

                if (self::hasData('ip_address')) {
                    $body .= ' (IP: ' . self::getData('ip_address') . ')';
                }

                return $body;
            // Follower notifications
            case NewFollower::class:
                $body = self::hasData('username') ? self::getData('username') : 'Someone';
                $body .= ' has started following you.';
                return $body;
            // Feed notifications
            case NewFeedMessageReply::class:
                $body = self::hasData('username') ? self::getData('username') : 'Someone';
                $body .= ' Replied to Your Message.';
                return $body;
            case NewFeedMessageReShare::class:
                $body = self::hasData('username') ? self::getData('username') : 'Someone';
                $body .= ' ReShared Your Message';
                return $body;
            // Anime import notifications
            case LibraryImportFinished::class:
                $serviceName = self::getData('service');
                $body = 'Your "' . $serviceName . '" anime import request has been processed.';

                if (self::hasData('successful_count')) {
                    $body .= ' ' . self::getData('successful_count') . ' Anime successfully imported.';
                }

                if (self::hasData('failure_count')) {
                    $body .= ' ' . self::getData('failure_count') . ' failed imports.';
                }

                return $body;
            // Subscription notifications
            case SubscriptionStatus::class:
                return self::getData('message') ?? '';
            // Local library import notifications
            case LocalLibraryImportFinished::class:
                $body = 'Your Local Library import was processed. Come check it out!';

                if (self::hasData('successful_count')) {
                    $body .= ' ' . self::getData('successful_count') . ' successfully imported.';
                }

                if (self::hasData('failure_count')) {
                    $body .= ' ' . self::getData('failure_count') . ' failed imports.';
                }

                return $body;
            // Unsupported library import notifications
            case LibraryImportUnsupported::class:
                return 'The file structure you submitted is not supported. Please reach out so we can fix this for you.';
            // Mention notifications
            case NewUserMention::class:
                return self::getData('title') ?? 'Someone mentioned you.';
        }

        return 'You have a new notification.';
    }

    /**
     * Return a localized string representation of the notification type.
     *
     * @return string
     */
    public function getLocalizedTypeAttribute(): string
    {
        return match ($this->type) {
            NewSession::class => __('New Session'),
            NewFollower::class => __('Follower'),
            NewFeedMessageReply::class, NewFeedMessageReShare::class => __('Message'),
            LibraryImportFinished::class, LocalLibraryImportFinished::class, LibraryImportUnsupported::class => __('Library Import'),
            NewUserMention::class => __('Mention'),
            SubscriptionStatus::class => __('Subscription Update'),
            default => __('Other')
        };
    }

    /**
     * The PNG asset slug for the notification's type icon tile.
     *
     * @return string
     */
    public function getIconAssetAttribute(): string
    {
        return match ($this->type) {
            NewFollower::class => 'follower',
            NewFeedMessageReply::class,
            NewFeedMessageReShare::class,
            NewUserMention::class => 'message',
            NewSession::class => 'session',
            LibraryImportFinished::class,
            LocalLibraryImportFinished::class,
            LibraryImportUnsupported::class => 'library',
            SubscriptionStatus::class => 'unlock',
            default => 'notifications',
        };
    }

    /**
     * The visual cell shape the notification should render with.
     *
     * @return string
     */
    public function getCellKindAttribute(): string
    {
        return match ($this->type) {
            NewFollower::class,
            NewFeedMessageReply::class,
            NewFeedMessageReShare::class => 'icon',
            default => 'basic',
        };
    }

    /**
     * Resolves a web URL the notification should navigate to when clicked.
     *
     * @return string|null
     */
    public function getDestinationUrlAttribute(): ?string
    {
        switch ($this->type) {
            case NewFollower::class:
                return $this->notifier ? route('profile.details', $this->notifier) : null;
            case NewFeedMessageReply::class:
            case NewFeedMessageReShare::class:
                $feedMessageID = self::getData('feedMessageID');
                return $feedMessageID !== null ? route('feed.details', $feedMessageID) : null;
            case NewUserMention::class:
                $link = self::getData('link');

                if ($link === null || str_contains($link, '/api/')) {
                    return null;
                }

                return $link;
            case NewSession::class:
            case SubscriptionStatus::class:
                return route('profile.settings');
            case LibraryImportFinished::class:
            case LibraryImportUnsupported::class:
            case LocalLibraryImportFinished::class:
            default:
                return null;
        }
    }

    /**
     * Whether the notification is unread.
     *
     * @return bool
     */
    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Whether the notification has been read.
     *
     * @return bool
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * The user that caused the notification to be sent.
     *
     * @return HasOne
     */
    public function notifier(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'data->userID');
    }

    /**
     * Checks whether the notification has data under the key value.
     *
     * @param string $key
     * @return bool
     */
    function hasData(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Gets a data variable from the notification or return null when
     * it doesn't exist.
     *
     * @param string $key
     * @return mixed
     */
    function getData(string $key): mixed
    {
        return self::hasData($key) ? $this->data[$key] : null;
    }
}
