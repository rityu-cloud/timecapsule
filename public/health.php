
<?php

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../vendor/autoload.php';

    // Загружаем настройки окружения, если приложение использует .env.
    if (class_exists(\Dotenv\Dotenv::class)) {
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->safeLoad();
    }

    $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
    $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '5432';
    $name = $_ENV['DB_DATABASE'] ?? $_ENV['DB_NAME'] ?? getenv('DB_DATABASE') ?: '';
    $user = $_ENV['DB_USERNAME'] ?? $_ENV['DB_USER'] ?? getenv('DB_USERNAME') ?: '';
    $password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';

    if ($name === '' || $user === '') {
        throw new RuntimeException('Database configuration is missing');
    }

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
    http_response_code(503);
    echo json_encode(['status' => 'error', 'db' => 'error']);
}
