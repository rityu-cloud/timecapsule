<?php

declare(strict_types=1);

$twigRoot = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'twig' . DIRECTORY_SEPARATOR . 'Twig-3.x';

require_once $twigRoot . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'core.php';
require_once $twigRoot . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'debug.php';
require_once $twigRoot . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'escaper.php';
require_once $twigRoot . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'string_loader.php';

spl_autoload_register(function (string $className) use ($twigRoot): void {
    $prefix = 'Twig\\';

    if (strncmp($className, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($className, strlen($prefix));
    $fileName = $twigRoot . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

    if (file_exists($fileName)) {
        require_once $fileName;
    }
});

