<?php

/**
 * @author Musa Semou <mussesemou99@gmail.com>
 */

namespace App\Helpers;

/**
    Class to sanitize variables
**/
class Sanitization {
    /**
        Cleans the variable
    **/
    public static function clean($value, $args = []) {
        // trim
        if(isset($args['trim']) && $args['trim'] == true)
            $value = trim($value);

        // strip_tags
        if(isset($args['strip_tags']) && $args['strip_tags'] == true)
            $value = strip_tags($value);

        // add_slashes
        if(isset($args['add_slashes']) && $args['add_slashes'] == true)
            $value = addslashes($value);

        // html_entities
        if(isset($args['html_entities']) && $args['html_entities'] == true)
            $value = htmlentities($value);

        return $value;
    }
}