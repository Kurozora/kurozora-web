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
use App\Notifications\UserTimedOut;
use App\Notifications\UserTimeoutExpired;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Carbon;
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
                return self::hasData('ip_address')
                    ? __('A new client has logged in to your account. (IP: :ipAddress)', ['ipAddress' => self::getData('ip_address')])
                    : __('A new client has logged in to your account.');
            // Follower notifications
            case NewFollower::class:
                return self::hasData('username')
                    ? __(':username has started following you.', ['username' => self::getData('username')])
                    : __('Someone has started following you.');
            // Feed notifications
            case NewFeedMessageReply::class:
                return self::hasData('username')
                    ? __(':username replied to your message.', ['username' => self::getData('username')])
                    : __('Someone replied to your message.');
            case NewFeedMessageReShare::class:
                return self::hasData('username')
                    ? __(':username reshared your message.', ['username' => self::getData('username')])
                    : __('Someone reshared your message.');
            // Anime import notifications
            case LibraryImportFinished::class:
                $messages = [__('Your ":service" anime import request has been processed.', ['service' => self::getData('service')])];

                if (self::hasData('successful_count')) {
                    $messages[] = trans_choice('{1} :count Anime successfully imported.|[2,*] :count Anime successfully imported.', (int) self::getData('successful_count'));
                }

                if (self::hasData('failure_count')) {
                    $messages[] = trans_choice('{1} :count failed import.|[2,*] :count failed imports.', (int) self::getData('failure_count'));
                }

                return implode(' ', $messages);
            // Subscription notifications
            case SubscriptionStatus::class:
                return self::getData('message') ?? '';
            // Local library import notifications
            case LocalLibraryImportFinished::class:
                $messages = [__('Your Local Library import was processed. Come check it out!')];

                if (self::hasData('successful_count')) {
                    $messages[] = trans_choice('{1} :count successfully imported.|[2,*] :count successfully imported.', (int) self::getData('successful_count'));
                }

                if (self::hasData('failure_count')) {
                    $messages[] = trans_choice('{1} :count failed import.|[2,*] :count failed imports.', (int) self::getData('failure_count'));
                }

                return implode(' ', $messages);
            // Unsupported library import notifications
            case LibraryImportUnsupported::class:
                return __('The file structure you submitted is not supported. Please reach out so we can fix this for you.');
            // Mention notifications
            case NewUserMention::class:
                return self::getData('title') ?? __('Someone mentioned you.');
            // Timeout notifications
            case UserTimedOut::class:
                $reason = self::getData('reasonLabel');

                if (self::getData('isPermanent')) {
                    return $reason !== null
                        ? __('Your account has been permanently suspended for :reason.', ['reason' => $reason])
                        : __('Your account has been permanently suspended.');
                }

                $expiry = self::hasData('expiresAt')
                    ? Carbon::createFromTimestamp(self::getData('expiresAt'))->translatedFormat('j M Y')
                    : null;

                if ($expiry !== null) {
                    return $reason !== null
                        ? __('Your account has been suspended until :expiry for :reason.', ['expiry' => $expiry, 'reason' => $reason])
                        : __('Your account has been suspended until :expiry.', ['expiry' => $expiry]);
                }

                return $reason !== null
                    ? __('Your account has been suspended for :reason.', ['reason' => $reason])
                    : __('Your account has been suspended.');
            case UserTimeoutExpired::class:
                return __('Your account suspension has ended. Welcome back!');
        }

        return __('You have a new notification.');
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
            UserTimedOut::class, UserTimeoutExpired::class => __('Moderation'),
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
            UserTimedOut::class, UserTimeoutExpired::class => 'shield_checkered',
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
