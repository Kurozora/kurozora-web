<?php

namespace App\Traits;

use App\Models\PersonalAccessToken;
use App\Models\Session;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

trait InteractsWithSessions
{
    /**
     * Returns the current browser session as a unified view object.
     *
     * @return object|null
     */
    protected function currentSessionView(): ?object
    {
        if (config('session.driver') !== 'database') {
            return null;
        }

        $session = Session::with(['sessionAttribute'])
            ->where([
                ['id', '=', request()->session()->getId()],
                ['user_id', '=', auth()->id()],
            ])
            ->first();

        return $session === null ? null : $this->sessionToView($session, true);
    }

    /**
     * Returns the user's other sessions (web and app), most recently active first.
     *
     * @return Collection
     */
    protected function otherSessionViews(): Collection
    {
        $currentSessionID = request()->session()->getId();

        $webSessions = Session::with(['sessionAttribute'])
            ->where('user_id', auth()->id())
            ->where('id', '!=', $currentSessionID)
            ->get()
            ->map(fn(Session $session) => $this->sessionToView($session, false));

        $appSessions = auth()->user()->tokens()
            ->with(['sessionAttribute'])
            ->get()
            ->map(fn(PersonalAccessToken $token) => $this->tokenToView($token));

        return $webSessions->concat($appSessions)
            ->sortByDesc('last_activity_at')
            ->values();
    }

    /**
     * Signs out the sessions identified by the given keys after confirming the password.
     *
     * @param array  $keys
     * @param string $password
     *
     * @return void
     * @throws ValidationException
     */
    protected function signOutSessions(array $keys, string $password): void
    {
        $this->confirmPassword($password);

        $currentSessionID = request()->session()->getId();
        $webSessionIDs = [];
        $appTokenIDs = [];

        foreach ($keys as $key) {
            [$type, $id] = array_pad(explode(':', $key, 2), 2, null);

            if ($type === 'web' && $id !== $currentSessionID) {
                $webSessionIDs[] = $id;
            } else if ($type === 'app') {
                $appTokenIDs[] = $id;
            }
        }

        if (!empty($webSessionIDs)) {
            Session::whereIn('id', $webSessionIDs)
                ->where('user_id', auth()->id())
                ->get()
                ->each
                ->delete();
        }

        if (!empty($appTokenIDs)) {
            auth()->user()->tokens()
                ->whereIn('id', $appTokenIDs)
                ->get()
                ->each
                ->delete();
        }
    }

    /**
     * Signs out every session except the current one after confirming the password.
     *
     * @param string $password
     *
     * @return void
     * @throws ValidationException
     */
    protected function signOutAllOtherSessions(string $password): void
    {
        $this->confirmPassword($password);

        Session::where('user_id', auth()->id())
            ->where('id', '!=', request()->session()->getId())
            ->get()
            ->each
            ->delete();

        auth()->user()->tokens()
            ->get()
            ->each
            ->delete();
    }

    /**
     * Confirms the given password matches the authenticated user.
     *
     * @param string $password
     *
     * @return void
     * @throws ValidationException
     */
    private function confirmPassword(string $password): void
    {
        if (!Hash::check($password, auth()->user()->password)) {
            throw ValidationException::withMessages([
                'password' => [__('This password does not match our records.')],
            ]);
        }
    }

    /**
     * Maps a web session to a unified view object.
     *
     * @param Session $session
     * @param bool    $isCurrent
     *
     * @return object
     */
    private function sessionToView(Session $session, bool $isCurrent): object
    {
        $sessionAttribute = $session->sessionAttribute;
        $lastActivity = Carbon::createFromTimestamp($session->last_activity);

        return (object) [
            'key' => 'web:' . $session->id,
            'full_platform' => $sessionAttribute?->full_platform ?? __('Unknown platform'),
            'full_location' => $sessionAttribute?->full_location ?? '',
            'app_source' => $sessionAttribute?->app_source,
            'device_model' => $sessionAttribute?->device_model,
            'device_symbol' => $this->deviceSymbol($sessionAttribute?->device_model),
            'ip_address' => $sessionAttribute?->ip_address ?? 'Unknown',
            'latitude' => $sessionAttribute?->latitude,
            'longitude' => $sessionAttribute?->longitude,
            'is_current' => $isCurrent,
            'last_activity' => $lastActivity->diffForHumans(),
            'last_activity_at' => $lastActivity,
        ];
    }

    /**
     * Maps an app session (access token) to a unified view object.
     *
     * @param PersonalAccessToken $token
     *
     * @return object
     */
    private function tokenToView(PersonalAccessToken $token): object
    {
        $sessionAttribute = $token->sessionAttribute;
        $lastUsed = $token->last_used_at;

        return (object) [
            'key' => 'app:' . $token->id,
            'full_platform' => $sessionAttribute?->full_platform ?? __('Unknown platform'),
            'full_location' => $sessionAttribute?->full_location ?? '',
            'app_source' => $sessionAttribute?->app_source,
            'device_model' => $sessionAttribute?->device_model,
            'device_symbol' => $this->deviceSymbol($sessionAttribute?->device_model),
            'ip_address' => $sessionAttribute?->ip_address ?? 'Unknown',
            'latitude' => $sessionAttribute?->latitude,
            'longitude' => $sessionAttribute?->longitude,
            'is_current' => false,
            'last_activity' => $lastUsed?->diffForHumans() ?? __('now'),
            'last_activity_at' => $lastUsed ?? Carbon::createFromTimestamp(0),
        ];
    }

    /**
     * Maps a device model to the name of its symbol.
     *
     * @param string|null $deviceModel
     *
     * @return string
     */
    private function deviceSymbol(?string $deviceModel): string
    {
        $deviceModel = strtolower($deviceModel ?? '');

        return match (true) {
            str_contains($deviceModel, 'iphone') => 'iphone',
            str_contains($deviceModel, 'ipad') => 'ipad_landscape',
            str_contains($deviceModel, 'tv') => 'appletv',
            str_contains($deviceModel, 'mac'), str_contains($deviceModel, 'windows'), str_contains($deviceModel, 'linux') => 'laptopcomputer',
            default => 'bolt_horizontal',
        };
    }
}
