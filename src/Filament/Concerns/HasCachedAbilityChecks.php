<?php

namespace Eclipse\Common\Filament\Concerns;

use Illuminate\Http\Request;

trait HasCachedAbilityChecks
{
    public static function canOnce(string $ability): bool
    {
        $request = request();
        $key = static::class.'.ability.'.$ability;

        if ($request instanceof Request) {
            if (! $request->attributes->has($key)) {
                $request->attributes->set($key, auth()->user()?->can($ability) ?? false);
            }

            return (bool) $request->attributes->get($key);
        }

        return auth()->user()?->can($ability) ?? false;
    }
}
