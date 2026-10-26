<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/db.php';

loadEnv(dirname(__DIR__) . '/.env');

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

function storage_client(): S3Client
{
    static $client = null;

    if ($client instanceof S3Client) {
        return $client;
    }

    $region = getenv('AWS_REGION') ?: 'eu-north-1';

    $client = new S3Client([
        'version' => 'latest',
        'region' => $region,
    ]);

    return $client;
}

function storage_bucket(): string
{
    $bucket = getenv('S3_BUCKET') ?: '';

    if ($bucket === '') {
        throw new RuntimeException('S3_BUCKET is not configured.');
    }

    return $bucket;
}

function storage_save(array $uploadedFile): string
{
    if (
        !isset($uploadedFile['error'], $uploadedFile['tmp_name']) ||
        $uploadedFile['error'] !== UPLOAD_ERR_OK ||
        !is_uploaded_file($uploadedFile['tmp_name'])
    ) {
        throw new RuntimeException('Uploaded file is invalid.');
    }

    $mimeType = mime_content_type($uploadedFile['tmp_name'])
        ?: 'application/octet-stream';

    if (!str_starts_with($mimeType, 'image/')) {
        throw new RuntimeException('Only image uploads are allowed.');
    }

    $extension = match ($mimeType) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/avif' => 'avif',
        default => throw new RuntimeException('Unsupported image type.'),
    };

    $randomName = bin2hex(random_bytes(16)) . '.' . $extension;

    storage_client()->putObject([
        'Bucket' => storage_bucket(),
        'Key' => 'uploads/' . $randomName,
        'SourceFile' => $uploadedFile['tmp_name'],
        'ContentType' => $mimeType,
    ]);

    return $randomName;
}

function storage_delete(string $name): void
{
    if (!preg_match('/\A[a-f0-9]{32}\.(jpg|png|gif|webp|avif)\z/', $name)) {
        throw new InvalidArgumentException('Invalid stored filename.');
    }

    storage_client()->deleteObject([
        'Bucket' => storage_bucket(),
        'Key' => 'uploads/' . $name,
    ]);
}

function storage_stream(
    string $name,
    string $mimeType = 'application/octet-stream',
    string $disposition = 'inline',
    ?string $originalName = null
): never {
    if (!preg_match('/\A[a-f0-9]{32}\.(jpg|png|gif|webp|avif)\z/', $name)) {
        http_response_code(400);
        exit('Invalid filename.');
    }

    if (!in_array($disposition, ['inline', 'attachment'], true)) {
        $disposition = 'inline';
    }

    $originalName = basename($originalName ?: $name);
    $safeName = str_replace(['"', "\r", "\n"], '', $originalName);

    $command = storage_client()->getCommand('GetObject', [
        'Bucket' => storage_bucket(),
        'Key' => 'uploads/' . $name,
        'ResponseContentType' => $mimeType,
        'ResponseContentDisposition' => $disposition . '; filename="' . $safeName . '"',
    ]);

    $request = storage_client()->createPresignedRequest(
        $command,
        '+5 minutes'
    );

    header('Location: ' . (string)$request->getUri(), true, 302);
    exit;
}
