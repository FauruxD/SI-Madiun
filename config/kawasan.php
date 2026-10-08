<?php

return [
    'documents' => [
        'disk' => env('KAWASAN_DOCUMENT_DISK', 'local'),
        'max_size_kb' => (int) env('KAWASAN_DOCUMENT_MAX_SIZE_KB', 10240),
        'allowed_types' => ['pdf', 'doc', 'docx'],
    ],
];
