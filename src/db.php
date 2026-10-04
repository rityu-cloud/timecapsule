<?php

declare(strict_types=1);

function loadEnv(string $file): void
{
    if (!file_exists($file)) {
        throw new RuntimeException('.env file not found');
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        [$name, $value] = array_pad(explode('=', $line, 2), 2, '');

        $name = trim($name);
        $value = trim($value);

        if ($name !== '') {
            putenv($name . '=' . $value);
        }
    }
}

function getDb(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        getenv('DB_HOST') ?: 'localhost',
        getenv('DB_PORT') ?: '5432',
        getenv('DB_NAME') ?: 'timecapsule'
    );

    $pdo = new PDO(
        $dsn,
        getenv('DB_USER') ?: 'timecapsule',
        getenv('DB_PASSWORD') ?: '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    return $pdo;
}