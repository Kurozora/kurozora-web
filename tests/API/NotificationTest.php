<?php

namespace Tests\API;

use App\Http\Resources\NotificationResource;
use App\Notifications\NewFollower;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Notifications\DatabaseNotification;
use Tests\Traits\ProvidesTestUser;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser;

    /**
     * Test if a user can get their own notifications.
     *
     * @return void
     * @test
     */
    function a_user_can_get_their_own_notifications()
    {
        // Add 10 notifications to the user
        $this->addNotificationsToUser($this->user, 10);

        // Send request to notifications endpoint
        $response = $this->auth()->json('GET', '/api/v1/users/' . $this->user->id . '/notifications');

        // Check whether the response is successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the response shows the 10 notifications
        $notificationArray = [];

        foreach($this->user->notifications as $notification)
            $notificationArray[] = NotificationResource::make($notification)->toArray();

        $response->assertJson([
            'notifications' => $notificationArray
        ]);
    }

    /**
     * Test if a user cannot get the notifications of someone else.
     *
     * @return void
     * @test
     */
    function a_user_cannot_get_the_notifications_of_someone_else()
    {
        /** @var User $otherUser */
        $otherUser = factory(User::class)->create();

        // Send request to notifications endpoint
        $response = $this->auth()->json('GET', '/api/v1/users/' . $otherUser->id . '/notifications');

        // Check whether the response is unsuccessful
        $response->assertUnsuccessfulAPIResponse();
    }

    /**
     * Test if a user can get the details of their notification.
     *
     * @return void
     * @test
     */
    function a_user_can_get_the_details_of_their_notification()
    {
        // Add a notification to the user
        $this->addNotificationsToUser($this->user, 1);

        // Get the user's first notification
        $notification = $this->user->notifications()->first();

        // Send request to notification endpoint
        $response = $this->auth()->json('GET', '/api/v1/notifications/' . $notification->id);

        // Check whether the response is successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the response shows the notification
        $response->assertJson([
            'notification' => NotificationResource::make($notification)->toArray()
        ]);
    }

    /**
     * Test if a user cannot get the details of another user's notification.
     *
     * @return void
     * @test
     */
    function a_user_cannot_get_the_details_of_another_users_notification()
    {
        /** @var User $otherUser */
        $otherUser = factory(User::class)->create();

        // Add a notification to the user
        $this->addNotificationsToUser($otherUser, 1);

        // Get the user's first notification
        $notification = $otherUser->notifications()->first();

        // Send request to notification endpoint
        $response = $this->auth()->json('GET', '/api/v1/notifications/' . $notification->id);

        // Check whether the response is unsuccessful
        $response->assertUnsuccessfulAPIResponse();
    }

    /**
     * Test if a user can delete their own notification.
     *
     * @return void
     * @test
     */
    function a_user_can_delete_their_own_notification()
    {
        // Add a notification to the user
        $this->addNotificationsToUser($this->user, 1);

        // Get the user's first notification
        $notification = $this->user->notifications()->first();

        // Send request to notification delete endpoint
        $response = $this->auth()->json('POST', '/api/v1/notifications/' . $notification->id . '/delete');

        // Check whether the response is successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the user now has no notifications
        $this->assertEquals(0, $this->user->notifications()->count());
    }

    /**
     * Test if a user cannot delete the notification of someone else.
     *
     * @return void
     * @test
     */
    function a_user_cannot_delete_the_notification_of_someone_else()
    {
        /** @var User $otherUser */
        $otherUser = factory(User::class)->create();

        // Add a notification to the user
        $this->addNotificationsToUser($otherUser, 1);

        // Get the user's first notification
        $notification = $otherUser->notifications()->first();

        // Send request to notification delete endpoint
        $response = $this->auth()->json('POST', '/api/v1/notifications/' . $notification->id . '/delete');

        // Check whether the response is unsuccessful
        $response->assertUnsuccessfulAPIResponse();
    }

    /**
     * Test if a user can mark a single notification as read.
     *
     * @return void
     * @test
     */
    function a_user_can_mark_a_single_notification_as_read()
    {
        // Add a notification to the user
        $this->addNotificationsToUser($this->user, 1);

        // Get the user's first notification
        $notification = $this->user->notifications()->first();

        // Send request to notification update endpoint
        $response = $this->auth()->json('POST', '/api/v1/notifications/update', [
            'notification'  => $notification->id,
            'read'          => 1
        ]);

        // Check whether the response is successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the notification is now read
        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    /**
     * Test if a user can mark a single notification as unread.
     *
     * @return void
     * @test
     */
    function a_user_can_mark_a_single_notification_as_unread()
    {
        // Add a notification to the user
        $this->addNotificationsToUser($this->user, 1);

        // Get the user's first notification
        /** @var DatabaseNotification $notification */
        $notification = $this->user->notifications()->first();

        // Mark the notification as read
        $notification->markAsRead();

        // Send request to notification update endpoint
        $response = $this->auth()->json('POST', '/api/v1/notifications/update', [
            'notification'  => $notification->id,
            'read'          => 0
        ]);

        // Check whether the response is successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the notification is now unread
        $notification->refresh();
        $this->assertNull($notification->read_at);
    }

    /**
     * Test if a user can mark all their notifications as read.
     *
     * @return void
     * @test
     */
    function a_user_can_mark_all_their_notifications_as_read()
    {
        // Add 20 notifications to the user
        $this->addNotificationsToUser($this->user, 20);

        // Get all of the user's notifications
        $notifications = $this->user->notifications()->get();

        // Create the string of IDs separated by comma
        $notificationIDs = '';

        foreach($notifications as $notification) {
            if(strlen($notificationIDs)) $notificationIDs .= ',';
            $notificationIDs .= $notification->id;
        }

        // Send request to notification update endpoint
        $response = $this->auth()->json('POST', '/api/v1/notifications/update', [
            'notification'  => $notificationIDs,
            'read'          => 1
        ]);

        // Check whether the response is successful
        $response->assertSuccessfulAPIResponse();

        // Check whether all the notifications are now read
        $notifications = $this->user->notifications()->get();

        foreach($notifications as $notification)
            $this->assertNotNull($notification->read_at);
    }

    /**
     * Test if a user can mark all their notifications as unread.
     *
     * @return void
     * @test
     */
    function a_user_can_mark_all_their_notifications_as_unread()
    {
        // Add 20 notifications to the user
        $this->addNotificationsToUser($this->user, 20);

        // Mark all of the notifications as read
        $this->user->notifications()->update(['read_at' => now()]);

        // Get all of the user's notifications
        $notifications = $this->user->notifications()->get();

        // Create the string of IDs separated by comma
        $notificationIDs = '';

        foreach($notifications as $notification) {
            if(strlen($notificationIDs)) $notificationIDs .= ',';
            $notificationIDs .= $notification->id;
        }

        // Send request to notification update endpoint
        $response = $this->auth()->json('POST', '/api/v1/notifications/update', [
            'notification'  => $notificationIDs,
            'read'          => 0
        ]);

        // Check whether the response is successful
        $response->assertSuccessfulAPIResponse();

        // Check whether all the notifications are now unread
        $notifications = $this->user->notifications()->get();

        foreach($notifications as $notification)
            $this->assertNull($notification->read_at);
    }

    /**
     * Test if a user can mark multiple notifications as read.
     *
     * @return void
     * @test
     */
    function a_user_can_mark_multiple_notifications_as_read()
    {
        // Add 20 notifications to the user
        $this->addNotificationsToUser($this->user, 20);

        // Get 10 of the user's notifications
        $notifications = $this->user->notifications()->limit(10)->get();

        // Create the string of IDs separated by comma
        $notificationIDs = '';

        foreach($notifications as $notification) {
            if(strlen($notificationIDs)) $notificationIDs .= ',';
            $notificationIDs .= $notification->id;
        }

        // Send request to notification update endpoint
        $response = $this->auth()->json('POST', '/api/v1/notifications/update', [
            'notification'  => $notificationIDs,
            'read'          => 1
        ]);

        // Check whether the response is successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the first 10 notifications are now read
        $notifications = $this->user->notifications()->limit(10)->get();

        foreach($notifications as $notification)
            $this->assertNotNull($notification->read_at);

        // Check whether the remaining 10 notifications are still unread
        $notifications = $this->user->notifications()->skip(10)->limit(10)->get();

        foreach($notifications as $notification)
            $this->assertNull($notification->read_at);
    }

    /**
     * Test if a user can mark multiple notifications as unread.
     *
     * @return void
     * @test
     */
    function a_user_can_mark_multiple_notifications_as_unread()
    {
        // Add 20 notifications to the user
        $this->addNotificationsToUser($this->user, 20);

        // Mark all of the notifications as read
        $this->user->notifications()->update(['read_at' => now()]);

        // Get 10 of the user's notifications
        $notifications = $this->user->notifications()->limit(10)->get();

        // Create the string of IDs separated by comma
        $notificationIDs = '';

        foreach($notifications as $notification) {
            if(strlen($notificationIDs)) $notificationIDs .= ',';
            $notificationIDs .= $notification->id;
        }

        // Send request to notification update endpoint
        $response = $this->auth()->json('POST', '/api/v1/notifications/update', [
            'notification'  => $notificationIDs,
            'read'          => 0
        ]);

        // Check whether the response is successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the first 10 notifications are now unread
        $notifications = $this->user->notifications()->limit(10)->get();

        foreach($notifications as $notification)
            $this->assertNull($notification->read_at);

        // Check whether the remaining 10 notifications are still read
        $notifications = $this->user->notifications()->skip(10)->limit(10)->get();

        foreach($notifications as $notification)
            $this->assertNotNull($notification->read_at);
    }

    /**
     * Test if a user cannot update notifications of others.
     *
     * @return void
     * @test
     */
    function a_user_cannot_update_notifications_of_others()
    {
        $this->markTestIncomplete('Users can currently update notifications of others(!)');
    }

    /**
     * Adds notifications to a user for testing.
     *
     * @param User $user
     * @param int $amount
     */
    private function addNotificationsToUser($user, $amount) {
        $otherUser = factory(User::class)->create();

        for($i = 0; $i < $amount; $i++)
            $user->notify(new NewFollower($otherUser));
    }
}
