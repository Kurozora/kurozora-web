<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class UserRole extends Enum
{
    const Normal        = 0;
    const Moderator     = 1;
    const Administrator = 2;
}
