<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/db.php';

loadEnv(__DIR__ . '/../.env');

$db = getDb();

$dataFile = __DIR__ . '/../data.json';

if (!file_exists($dataFile)) {
    throw new RuntimeException('data.json not found');
}

$json = file_get_contents($dataFile);
$transactions = json_decode((string)$json, true);

if (!is_array($transactions)) {
    throw new RuntimeException('Invalid JSON data');
}

$statement = $db->prepare(
    'INSERT INTO transactions
        (id, date, amount, description, merchant)
     VALUES
        (:id, :date, :amount, :description, :merchant)
     ON CONFLICT (id) DO NOTHING'
);

$count = 0;

foreach ($transactions as $transaction) {
    $statement->execute([
        'id' => (int)$transaction['id'],
        'date' => $transaction['date'],
        'amount' => (float)$transaction['amount'],
        'description' => $transaction['description'],
        'merchant' => $transaction['merchant'],
    ]);

    if ($statement->rowCount() > 0) {
        $count++;
    }
}

echo "Imported {$count} transactions.\n";