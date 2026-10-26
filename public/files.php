<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../src/storage.php';
require_once __DIR__ . '/../src/gallery_auth.php';

requireGalleryAuth();

if (isset($_GET['view'])) {
    $name = (string)$_GET['view'];

    if (!preg_match('/\\A[a-f0-9]{32}\\.(jpg|png|gif|webp|avif)\\z/', $name)) {
        http_response_code(400);
        exit('Invalid image name.');
    }

    storage_stream($name, imageMime($name), 'inline', $name);
}

if (empty($_SESSION['files_csrf'])) {
    $_SESSION['files_csrf'] = bin2hex(random_bytes(32));
}

$message = '';
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $csrf = (string)($_POST['csrf'] ?? '');

    if (!hash_equals($_SESSION['files_csrf'], $csrf)) {
        http_response_code(403);
        exit('Invalid security token.');
    }

    try {
        $action = (string)($_POST['action'] ?? '');

        if ($action === 'upload') {
            $name = storage_save($_FILES['image'] ?? []);
            $message = 'Image uploaded: ' . $name;
        } elseif ($action === 'delete') {
            $name = (string)($_POST['name'] ?? '');
            storage_delete($name);
            $message = 'Image deleted: ' . $name;
        } else {
            throw new RuntimeException('Unknown action.');
        }
    } catch (Throwable $e) {
        error_log('S3 gallery error: ' . $e->getMessage());
        $error = 'Operation failed. Check the server error log.';
    }
}

$images = [];

try {
    $result = storage_client()->listObjectsV2([
        'Bucket' => storage_bucket(),
        'Prefix' => 'uploads/',
    ]);

    foreach ($result['Contents'] ?? [] as $object) {
        $key = (string)($object['Key'] ?? '');
        $name = basename($key);

        if (
            $key !== 'uploads/' &&
            preg_match('/\A[a-f0-9]{32}\.(jpg|png|gif|webp|avif)\z/', $name)
        ) {
            $images[] = [
                'name' => $name,
                'size' => (int)($object['Size'] ?? 0),
            ];
        }
    }
} catch (Throwable $e) {
    error_log('S3 gallery list error: ' . $e->getMessage());
    $error = 'Could not load images from S3. Check the server error log.';
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function imageMime(string $name): string
{
    return match (strtolower(pathinfo($name, PATHINFO_EXTENSION))) {
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'avif' => 'image/avif',
        default => 'application/octet-stream',
    };
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>S3 Image Gallery</title>
    <style>
        body { font: 16px system-ui, sans-serif; max-width: 900px; margin: 40px auto; padding: 0 18px; }
        .message { color: #176b36; }
        .error { color: #b42318; }
        .images { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; }
        .image { border: 1px solid #ddd; border-radius: 8px; padding: 14px; overflow-wrap: anywhere; }
        button { padding: 8px 12px; cursor: pointer; }
        form { margin: 16px 0; }
    </style>
</head>
<body>
    <h1>S3 Image Gallery</h1>

    <?php if ($message !== ''): ?>
        <p class="message"><?= h($message) ?></p>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <p class="error"><?= h($error) ?></p>
    <?php endif; ?>

    <h2>Upload an image</h2>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= h($_SESSION['files_csrf']) ?>">
        <input type="hidden" name="action" value="upload">
        <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp,image/avif" required>
        <button type="submit">Upload to S3</button>
    </form>

    <h2>Images in S3</h2>

    <?php if (!$images): ?>
        <p>No gallery images found.</p>
    <?php else: ?>
        <div class="images">
            <?php foreach ($images as $image): ?>
                <article class="image">
                    <p><strong><?= h($image['name']) ?></strong></p>
                    <p><?= number_format($image['size']) ?> bytes</p>
                    <p>
                        <a href="files.php?view=<?= rawurlencode($image['name']) ?>">View image</a>
                    </p>
                    <form method="post" onsubmit="return confirm('Delete this image from S3?')">
                        <input type="hidden" name="csrf" value="<?= h($_SESSION['files_csrf']) ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="name" value="<?= h($image['name']) ?>">
                        <button type="submit">Delete</button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>
</html>
