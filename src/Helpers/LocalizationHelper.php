<?php

namespace Eclipse\Common\Helpers;

use Locale;

class LocalizationHelper
{
    /**
     * Get the language name from the language code.
     * 1. If the `intl` PHP extension is installed, use the `\Locale` class to get the language name.
     * 2. Otherwise, return the language code
     *
     * @param string $code Language code
     * @param bool $include_code Include the language code in the output (in parentheses)
     * @return string Language name
     */
    public static function getLanguageNameFromCode(string $code, bool $include_code = false): string
    {
        $code = trim(str_replace('_', '-', $code));

        if ($code === '') {
            return '';
        }

        $name = Locale::getDisplayLanguage($code, config('app.locale'));

        if (is_string($name) && ! empty($name)) {
            return $name . ($include_code ? ' (' . $code . ')' : '');
        }

        return $code;
    }
}
