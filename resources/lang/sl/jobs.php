<?php

return [

    'notifications' => [
        'queued' => [
            'title' => 'Opravilo ":job" postavljeno v vrsto.',
            'message' => 'Opravilo je postavljeno v vrsto za izvajanje.',
        ],
        'completed' => [
            'title' => 'Opravilo ":job" uspešno zaključeno!',
            'message' => 'Opravilo je bilo uspešno zaključeno.',
        ],
        'failed' => [
            'title' => 'Opravilo ":job" spodletelo!',
            'message' => 'Pri izvajanju opravila je prišlo do napake. Sporočilo napake: :exception',
        ],
    ],
];
