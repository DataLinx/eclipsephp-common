<?php

namespace Workbench\App\Settings;

use Spatie\LaravelSettings\Settings;

class ExampleSettings extends Settings
{
    public ?int $number_setting;

    public ?string $string_setting;

    public static function group(): string
    {
        return 'example';
    }
}
