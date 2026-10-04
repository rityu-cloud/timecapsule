<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/db.php';

loadEnv(__DIR__ . '/../.env');

$db = getDb();

$db->exec(
    <<<SQL
    CREATE TABLE IF NOT EXISTS transactions (
        id INTEGER PRIMARY KEY,
        date DATE NOT NULL,
        amount NUMERIC(12, 2) NOT NULL,
        description TEXT NOT NULL,
        merchant TEXT NOT NULL
    )
    SQL
);

echo "Database initialized successfully.\n";