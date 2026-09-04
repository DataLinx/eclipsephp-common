<?php

namespace Tests\Support;

class ConfigLocaleCallback
{
    public static function getLocales(): array
    {
        return ['en', 'sl', 'de'];
    }
}
