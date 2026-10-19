
<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

try {
    require_once dirname(__DIR__) . '/src/db.php';

    $pdo = getDb();
    $pdo->query('SELECT 1');

    echo json_encode([
        'status' => 'ok',
        'db' => 'ok',
    ]);
} catch (Throwable $e) {
    error_log('Health check failed: ' . $e->getMessage());

    http_response_code(503);

    echo json_encode([
        'status' => 'error',
        'db' => 'error',
    ]);
}
