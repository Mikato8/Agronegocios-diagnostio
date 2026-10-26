<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'AgroNegocios MX'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env_bool('APP_DEBUG', false),
    'url' => rtrim(env('APP_URL', 'http://127.0.0.1:8080'), '/'),
    'timezone' => env('APP_TIMEZONE', 'America/Mexico_City'),
    'locale' => env('APP_LOCALE', 'es_MX'),
    'key' => env('APP_KEY', ''),
    'auto_verify_users' => env_bool('AUTO_VERIFY_USERS', false),

    'db' => [
        'url' => env('DATABASE_URL', ''),
        'unpooled_url' => env('DATABASE_URL_UNPOOLED', ''),
        'driver' => env('DB_DRIVER', 'auto'), // auto | http | pdo
    ],

    'session' => [
        'name' => env('SESSION_NAME', 'agronegocios_session'),
        'lifetime' => (int) env('SESSION_LIFETIME', '7200'),
        'secure' => env_bool('SESSION_SECURE', str_starts_with((string) env('APP_URL', ''), 'https://')),
    ],

    'uploads' => [
        'images_path' => BASE_PATH . '/public/uploads/casos',
        'images_url' => '/uploads/casos',
        'files_path' => BASE_PATH . '/public/uploads/archivos',
        'files_url' => '/uploads/archivos',
        'avatars_path' => BASE_PATH . '/public/uploads/avatares',
        'avatars_url' => '/uploads/avatares',
        'proofs_path' => BASE_PATH . '/storage/constancias',
        'max_bytes' => (int) env('UPLOAD_MAX_BYTES', (string) (8 * 1024 * 1024)),
        'max_files' => (int) env('UPLOAD_MAX_FILES', '4'),
        'max_document_bytes' => (int) env('UPLOAD_MAX_DOCUMENT_BYTES', (string) (12 * 1024 * 1024)),
    ],

    'mail' => [
        'enabled' => env_bool('MAIL_ENABLED', false),
        'from' => env('MAIL_FROM', 'no-reply@agronegocios.mx'),
        'from_name' => env('MAIL_FROM_NAME', 'AgroNegocios MX'),
    ],

    'pagination' => [
        'cases' => 9,
        'reports' => 9,
        'opportunities' => 9,
        'admin' => 20,
    ],

    'roles' => [
        'estudiante' => 'Estudiante',
        'docente' => 'Docente',
        'editor' => 'Editor',
        'admin' => 'Administrador',
    ],

    'moderators' => ['editor', 'admin'],
];
