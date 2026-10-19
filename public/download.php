<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../src/db.php';

loadEnv(__DIR__ . '/../.env');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Access denied.');
}

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    exit('Invalid capsule ID.');
}

$db = getDb();

$statement = $db->prepare(
    'SELECT file_name, file_path
     FROM capsules
     WHERE id = :id AND user_id = :user_id'
);

$statement->execute([
    'id' => $id,
    'user_id' => $_SESSION['user_id'],
]);

$capsule = $statement->fetch();

if (!$capsule || empty($capsule['file_path'])) {
    http_response_code(404);
    exit('File not found.');
}

$filePath = dirname(__DIR__) . '/' . $capsule['file_path'];

if (!is_file($filePath)) {
    http_response_code(404);
    exit('File not found.');
}

$mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

header('Content-Type: ' . $mimeType);
header(
    'Content-Disposition: attachment; filename="' .
    basename($capsule['file_name']) .
    '"'
);
header('Content-Length: ' . filesize($filePath));

readfile($filePath);
exit;