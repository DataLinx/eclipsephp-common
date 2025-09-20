<?php

namespace Eclipse\Common\Helpers;

class MediaHelper
{
    public static function getPlaceholderImageUrl(?string $text = null, int $width = 120, int $height = 120): string
    {
        $svg = view('eclipse-common::components.placeholder-image', [
            'text' => $text,
            'width' => $width,
            'height' => $height,
        ])->render();

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
