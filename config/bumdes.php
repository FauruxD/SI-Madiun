<?php

return [
    'website' => [
        'base_domain' => env('BUMDES_WEBSITE_BASE_DOMAIN', 'bumdes.localhost'),
        'verified_custom_domains' => array_values(array_filter(array_map(
            fn (string $domain): string => strtolower(trim($domain)),
            explode(',', (string) env('BUMDES_VERIFIED_CUSTOM_DOMAINS', '')),
        ))),
    ],
    'documents' => [
        'disk' => env('BUMDES_DOCUMENT_DISK', 'local'),
        'max_size_kb' => (int) env('BUMDES_DOCUMENT_MAX_SIZE_KB', 10240),
        'allowed_types' => ['pdf', 'doc', 'docx'],
    ],
    'images' => [
        'disk' => env('BUMDES_IMAGE_DISK', 'local'),
        'max_size_kb' => (int) env('BUMDES_IMAGE_MAX_SIZE_KB', 5120),
        'allowed_types' => ['jpg', 'jpeg', 'png', 'webp'],
    ],
];
