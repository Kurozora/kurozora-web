<?php

namespace App\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Queue\SerializesModels;

class RecoveryCodeReplaced
{
    use SerializesModels;

    /**
     * The authenticated user.
     *
     * @var Authenticatable
     */
    public Authenticatable $user;

    /**
     * The recovery code.
     *
     * @var string
     */
    public string $code;

    /**
     * Create a new event instance.
     *
     * @param Authenticatable $user
     * @param string $code
     * @return void
     */
    public function __construct(Authenticatable $user, string $code)
    {
        $this->user = $user;
        $this->code = $code;
    }
}
