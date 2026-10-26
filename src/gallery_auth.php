<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

loadEnv(dirname(__DIR__) . '/.env');

function requireGalleryAuth(): void
{
    $expectedUser = getenv('GALLERY_USER') ?: '';
    $expectedPassword = getenv('GALLERY_PASSWORD') ?: '';

    if ($expectedUser === '' || $expectedPassword === '') {
        http_response_code(503);
        exit('Gallery authentication is not configured.');
    }

    $user = $_SERVER['PHP_AUTH_USER'] ?? '';
    $password = $_SERVER['PHP_AUTH_PW'] ?? '';

    if (
        !hash_equals($expectedUser, $user) ||
        !hash_equals($expectedPassword, $password)
    ) {
        header('WWW-Authenticate: Basic realm="Time Capsule Gallery"');
        http_response_code(401);
        exit('Authentication required.');
    }
}
