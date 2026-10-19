<?php

declare(strict_types=1);

function loadEnv(string $file): void
{
    if (!file_exists($file)) {
        throw new RuntimeException('.env file not found');
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        [$name, $value] = array_pad(explode('=', $line, 2), 2, '');

        $name = trim($name);
        $value = trim($value);

        if ($name !== '') {
            putenv($name . '=' . $value);
        }
    }
}

function getDb(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $envFile = dirname(__DIR__) . '/.env';
    if (is_file($envFile)) {
        loadEnv($envFile);
    }

    // Use the configured PostgreSQL database when its PDO driver is installed.
    // A local SQLite database keeps the project runnable on PHP installations
    // that ship with pdo_sqlite but do not have pdo_pgsql enabled.
    if (in_array('pgsql', PDO::getAvailableDrivers(), true)) {
        $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        getenv('DB_HOST') ?: 'localhost',
        getenv('DB_PORT') ?: '5432',
        getenv('DB_NAME') ?: 'timecapsule'
        );

        $pdo = new PDO(
            $dsn,
            getenv('DB_USER') ?: 'timecapsule',
            getenv('DB_PASSWORD') ?: '',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );

        return $pdo;
    }

    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        throw new RuntimeException('PHP must have either pdo_pgsql or pdo_sqlite enabled.');
    }

    $databasePath = dirname(__DIR__) . '/transactions.sqlite';
    $pdo = new PDO('sqlite:' . $databasePath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS transactions (
            id INTEGER PRIMARY KEY,
            date TEXT NOT NULL,
            amount NUMERIC NOT NULL,
            description TEXT NOT NULL,
            merchant TEXT NOT NULL
        )'
    );

    $count = (int)$pdo->query('SELECT COUNT(*) FROM transactions')->fetchColumn();
    $dataFile = dirname(__DIR__) . '/data.json';
    if ($count === 0 && is_file($dataFile)) {
        $transactions = json_decode((string)file_get_contents($dataFile), true);
        if (is_array($transactions)) {
            $insert = $pdo->prepare(
                'INSERT OR IGNORE INTO transactions
                    (id, date, amount, description, merchant)
                 VALUES (:id, :date, :amount, :description, :merchant)'
            );
            foreach ($transactions as $transaction) {
                $insert->execute([
                    'id' => (int)$transaction['id'],
                    'date' => $transaction['date'],
                    'amount' => (float)$transaction['amount'],
                    'description' => $transaction['description'],
                    'merchant' => $transaction['merchant'],
                ]);
            }
        }
    }

    return $pdo;
}
