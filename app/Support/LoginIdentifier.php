<?php

namespace App\Support;

class LoginIdentifier
{
    public static function normalize(string $identifier, ?string $domain): string
    {
        if (str_contains($identifier, '@')) {
            return $identifier;
        }

        if (! $domain) {
            return $identifier;
        }

        return $identifier.'@'.$domain;
    }
}
