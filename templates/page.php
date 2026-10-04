<?php

ob_start();
require __DIR__ . '/form.php';
require __DIR__ . '/list.php';
$content = ob_get_clean();

require __DIR__ . '/layout.php';

