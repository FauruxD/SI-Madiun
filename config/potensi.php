<?php

return [
    'images' => [
        'disk' => env('POTENSI_IMAGE_DISK', 'local'),
        'max_size_kb' => (int) env('POTENSI_IMAGE_MAX_SIZE_KB', 5120),
        'allowed_types' => ['jpg', 'jpeg', 'png', 'webp'],
    ],
];
