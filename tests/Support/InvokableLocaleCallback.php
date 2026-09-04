<?php

namespace Tests\Support;

class InvokableLocaleCallback
{
    public function __invoke(): array
    {
        return ['en', 'sl', 'de'];
    }
}
