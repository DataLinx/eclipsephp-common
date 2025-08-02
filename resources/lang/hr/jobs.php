<?php

return [

    'notifications' => [
        'queued' => [
            'title' => 'Zadatak ":job" je stavljen u red čekanja.',
            'message' => 'Zadatak je stavljen u red čekanja za izvršavanje.',
        ],
        'completed' => [
            'title' => 'Zadatak ":job" uspješno završen!',
            'message' => 'Zadatak je uspješno završen.',
        ],
        'failed' => [
            'title' => 'Zadatak ":job" nije uspio!',
            'message' => 'Došlo je do pogreške prilikom izvršavanja zadatka. Poruka o pogrešci: :exception',
        ],
    ],
];
