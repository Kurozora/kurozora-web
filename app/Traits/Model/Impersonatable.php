<?php

namespace App\Traits\Model;

use Laravel\Nova\Auth\Impersonatable as BaseImpersonatable;

if (trait_exists('Laravel\Nova\Auth\Impersonatable')) {
    trait Impersonatable
    {
        use BaseImpersonatable;
    }
} else {
    trait Impersonatable {}
}
