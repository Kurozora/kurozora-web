<?php

namespace App\Traits\Model;

use Laravel\Nova\Actions\Actionable as BaseActionable;

if (trait_exists('Laravel\Nova\Actions\Actionable')) {
    trait Actionable {
        use BaseActionable;
    }
} else {
    trait Actionable {}
}
