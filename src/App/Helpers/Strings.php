<?php

namespace App\Helpers;

class Strings {

    public static function toCamelCase($string): string {
        $string = str_replace(['-', '_'], ' ', $string);
        return str_replace(' ', '', lcfirst(ucwords($string)));
    }
}
