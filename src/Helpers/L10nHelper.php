<?php

namespace Eclipse\Common\Helpers;

use Eclipse\Common\Exceptions\InvalidConfigurationException;
use Locale;

class L10nHelper
{
    /**
     * Get the available locale codes from the configuration.
     *
     * @return string[]
     *
     * @throws InvalidConfigurationException
     */
    public static function getAvailableLocales(): array
    {
        $config = config('eclipse-common.available_locales');

        if (is_array($config)) {
            $locales = $config;
        } elseif (is_callable($config)) {
            $locales = $config();
        } else {
            throw new InvalidConfigurationException('Configuration "eclipse-common.available_locales" must be an array or a callable that returns an array.');
        }

        if (empty($locales)) {
            throw new InvalidConfigurationException('Configuration "eclipse-common.available_locales" must contain at least one locale.');
        }

        // Return the array with values as keys
        return array_combine($locales, $locales);
    }

    /**
     * Get a list of available locales for the application.
     * Returns an array of locale codes (as keys) and their corresponding names (as values).
     *
     * @return string[]
     *
     * @throws InvalidConfigurationException
     */
    public static function getLocaleOptions(): array
    {
        return array_map(fn ($locale) => static::getLanguageName($locale, true), static::getAvailableLocales());
    }

    /**
     * Get the language name from the language code.
     * 1. If the `intl` PHP extension is installed, use the `\Locale` class to get the language name.
     * 2. Otherwise, return the language code
     *
     * @param  string  $code  Language code
     * @param  bool  $include_code  Include the language code in the output (in parentheses)
     * @return string Language name
     */
    public static function getLanguageName(string $code, bool $include_code = false): string
    {
        $code = trim(str_replace('_', '-', $code));

        if ($code === '') {
            return '';
        }

        $name = Locale::getDisplayLanguage($code, config('app.locale'));

        if (is_string($name) && ! empty($name)) {
            return $name.($include_code ? ' ('.$code.')' : '');
        }

        return $code;
    }
}
