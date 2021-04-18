<?php

namespace App\Helpers;

use App\Models\User;
use Arr;
use Exception;
use JetBrains\PhpStorm\Pure;

class Settings
{
    /**
     * The User object.
     *
     * @var User
     */
    protected User $user;

    /**
     * The list of settings.
     *
     * @var array
     */
    protected array $settings = [];

    /**
     * Create a new settings instance.
     *
     * @param array $settings
     * @param User  $user
     */
    public function __construct(array $settings, User $user)
    {
        $this->settings = $settings;
        $this->user = $user;
    }

    /**
     * Magic property access for settings.
     *
     * @param string $key
     * @return mixed
     * @throws Exception
     */
    public function __get(string $key): mixed
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        throw new Exception("The $key setting does not exist.");
    }

    /**
     * Create a new settings object for the given user.
     *
     * @param User $user
     * @return Settings
     */
    #[Pure]
    public static function create(User $user): Settings
    {
        return new Settings($user->settings, $user);
    }

    /**
     * Retrieve an array of all settings.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->settings;
    }

    /**
     * Retrieve the given setting.
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed
    {
        return Arr::get($this->settings, $key);
    }

    /**
     * Create and persist a new setting.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function set(string $key, mixed $value): bool
    {
        $this->settings[$key] = $value;

        return $this->persist();
    }

    /**
     * Determine if the given setting exists.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->settings);
    }

    /**
     * Merge the given attributes with the current settings without assigning any "new" settings.
     *
     * @param array $attributes
     * @return bool
     */
    public function merge(array $attributes): bool
    {
        $this->settings = array_merge(
            $this->settings,
            Arr::only($attributes, array_keys($this->settings))
        );

        return $this->persist();
    }

    /**
     * Persist the settings.
     *
     * @return bool
     */
    protected function persist(): bool
    {
        return $this->user->update(['settings' => $this->settings]);
    }

    /**
     * The settings of the user.
     *
     * Provided both arguments to set a value. Provide only one to get a value.
     * If none is provided, then the Settings object is returned.
     *
     * @param ?string $key
     * @param mixed $value
     * @return mixed
     */
    public function settings(?string $key = null, mixed $value = null): mixed
    {
        if (empty($key) && empty($value)) {
            return $this;
        }

        if (empty($value)) {
            return $this->get($key);
        }

        return $this->set($key, $value);
    }
}
