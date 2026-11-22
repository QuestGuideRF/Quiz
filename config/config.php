<?php
declare(strict_types=1);
return [
    'app' => [
        'name' => 'Домнин Викторина',
        'env' => 'production',
        'debug' => false,
        'base_url' => 'https://domninvyacheslav.online',
        'timezone' => 'Europe/Moscow',
        'default_meta_description' => 'Развлекательные тесты и квизы на русском языке с удобным мобильным интерфейсом.',
    ],
    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'u3481923_tests',
        'username' => 'u3481923_tests',
        'password' => 'u3481923_tests',
        'charset' => 'utf8mb4',
    ],
    'session' => [
        'name' => 'domnin_quiz_session',
        'lifetime' => 7200,
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ],
    'uploads' => [
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
        'allowed_mime_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
        'max_size' => 2 * 1024 * 1024,
        'directories' => [
            'tests' => '/uploads/tests',
            'questions' => '/uploads/questions',
            'results' => '/uploads/results',
        ],
    ],
];