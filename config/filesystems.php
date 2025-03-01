<?php

return [
    'disks' => [
        'unifersa' => [
            'driver' => 'ftp',
            'host' => env('FTP_HOST'),
            'username' => env('FTP_USERNAME'),
            'password' => env('FTP_PASSWORD'),
            // Optional FTP Settings...
            // 'port' => env('FTP_PORT', 21),
            'root' => env('FTP_DIRECTORY'),
            // 'passive' => true,
            // 'ssl' => true,
            // 'timeout' => 30,
        ],
    ],
];
