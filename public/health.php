
<?php

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../vendor/autoload.php';

    if (class_exists(\Dotenv\Dotenv::class)) {
        \Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
    }

    $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
    $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '5432';
    $name = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'timecapsule';
    $user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'timecapsule';
    $password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';

    $pdo = new PDO(
        "pgsql:host={$host};port={$port};dbname={$name}",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 3,
        ]
    );

    $pdo->query('SELECT 1');

    http_response_code(200);
    echo json_encode(['status' => 'ok', 'db' => 'ok']);

} catch (Throwable $e) {
    error_log('Health check failed: ' . $e->getMessage());

    http_response_code(503);
    echo json_encode(['status' => 'error', 'db' => 'error']);
}
