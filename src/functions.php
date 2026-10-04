<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';


function loadTransactions(): array
{
    $db = getDb();

    $statement = $db->query(
        'SELECT id, date, amount, description, merchant
         FROM transactions
         ORDER BY id'
    );

    return $statement->fetchAll();
}


function calculateTotalAmount(array $transactions): float
{
    $totalAmount = 0;

    foreach ($transactions as $transaction) {
        $totalAmount += (float)$transaction['amount'];
    }

    return $totalAmount;
}


function findTransactionByDescription(
    array $transactions,
    string $descriptionPart
): array {
    $suitableTransactions = [];

    foreach ($transactions as $transaction) {
        if (
            stripos(
                (string)$transaction['description'],
                $descriptionPart
            ) !== false
        ) {
            $suitableTransactions[] = $transaction;
        }
    }

    return $suitableTransactions;
}


function findTransactionById(
    array $transactions,
    int $id
): ?array {
    foreach ($transactions as $transaction) {
        if ((int)$transaction['id'] === $id) {
            return $transaction;
        }
    }

    return null;
}


function daysSinceTransaction(string $date): int
{
    $tz = ini_get('date.timezone') ?: 'Europe/Chisinau';

    $dtz = new DateTimeZone($tz);

    $currentDate = new DateTime('now', $dtz);
    $transactionDate = new DateTime($date, $dtz);

    return (int)$transactionDate->diff($currentDate)->days;
}


function addTransaction(
    array &$transactions,
    array $newTransaction
): string {
    $db = getDb();


    $check = $db->prepare(
        'SELECT id
         FROM transactions
         WHERE id = :id'
    );

    $check->execute([
        'id' => (int)$newTransaction['id']
    ]);

    if ($check->fetch()) {
        return 'Tranzakciya s takim id uzhe est';
    }
    $insert = $db->prepare(
        'INSERT INTO transactions
            (id, date, amount, description, merchant)
         VALUES
            (:id, :date, :amount, :description, :merchant)'
    );

    $insert->execute([
        'id' => (int)$newTransaction['id'],
        'date' => $newTransaction['date'],
        'amount' => (float)$newTransaction['amount'],
        'description' => $newTransaction['description'],
        'merchant' => $newTransaction['merchant'],
    ]);

    $transactions[] = $newTransaction;

    return 'Tranzakciya dobavlena';
}


function sortTransactionsByDate(array $transactions): array
{
    usort(
        $transactions,
        function (
            array $first,
            array $second
        ): int {
            $firstDate = new DateTime($first['date']);
            $secondDate = new DateTime($second['date']);

            if ($firstDate > $secondDate) {
                return 1;
            }

            if ($secondDate > $firstDate) {
                return -1;
            }

            return 0;
        }
    );

    return $transactions;
}


function sortTransactionsByAmount(array $transactions): array
{
    usort(
        $transactions,
        function (
            array $first,
            array $second
        ): int {
            if ($first['amount'] > $second['amount']) {
                return 1;
            }

            if ($second['amount'] > $first['amount']) {
                return -1;
            }

            return 0;
        }
    );

    return $transactions;
}