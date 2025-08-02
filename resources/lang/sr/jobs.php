<?php

return [

    'notifications' => [
        'queued' => [
            'title' => 'Zadatak ":job" je stavljen u red čekanja.',
            'message' => 'Zadatak je stavljen u red čekanja za izvršavanje.',
        ],
        'completed' => [
            'title' => 'Zadatak ":job" uspešno završen!',
            'message' => 'Zadatak je uspešno završen.',
        ],
        'failed' => [
            'title' => 'Zadatak ":job" nije uspeo!',
            'message' => 'Došlo je do greške prilikom izvršavanja zadatka. Poruka o grešci: :exception',
        ],
    ],
];
