<?php

return [

    'notifications' => [
        'queued' => [
            'title' => 'Task ":job" has been queued.',
            'message' => 'The task has been queued for execution.',
        ],
        'completed' => [
            'title' => 'Task ":job" successfully completed!',
            'message' => 'The task has been successfully completed.',
        ],
        'failed' => [
            'title' => 'Task ":job" failed!',
            'message' => 'An error occurred while executing the task. Error message: :exception',
        ],
    ],
];
