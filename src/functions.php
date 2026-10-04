<?php

declare(strict_types=1);

function loadTransactions(string $fileName): array
{
    if (!file_exists($fileName)) {
        return [];
    }

    $json = file_get_contents($fileName);
    $transactions = json_decode((string)$json, true);

    if (!is_array($transactions)) {
        return [];
    }

    return $transactions;
}

function saveTransactions(string $fileName, array $transactions): void
{
    file_put_contents($fileName, json_encode($transactions, JSON_PRETTY_PRINT));
}

function calculateTotalAmount(array $transactions): float
{
    $totalAmount = 0;

    foreach ($transactions as $transaction) {
        $totalAmount += (float)$transaction['amount'];
    }

    return $totalAmount;
}

function findTransactionByDescription(array $transactions, string $descriptionPart): array
{
    $suitableTransactions = [];

    foreach ($transactions as $transaction) {
        if (stripos($transaction['description'], $descriptionPart) !== false) {
            $suitableTransactions[] = $transaction;
        }
    }

    return $suitableTransactions;
}

function findTransactionById(array $transactions, int $id): ?array
{
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

function addTransaction(array &$transactions, array $newTransaction): string
{
    foreach ($transactions as $transaction) {
        if ((int)$transaction['id'] === (int)$newTransaction['id']) {
            return 'Tranzakciya s takim id uzhe est';
        }
    }

    $transactions[] = $newTransaction;

    return 'Tranzakciya dobavlena';
}

function sortTransactionsByDate(array $transactions): array
{
    usort($transactions, function (array $first, array $second): int {
        $firstDate = new DateTime($first['date']);
        $secondDate = new DateTime($second['date']);

        if ($firstDate > $secondDate) {
            return 1;
        } elseif ($secondDate > $firstDate) {
            return -1;
        }

        return 0;
    });

    return $transactions;
}

function sortTransactionsByAmount(array $transactions): array
{
    usort($transactions, function (array $first, array $second): int {
        if ($first['amount'] > $second['amount']) {
            return 1;
        } elseif ($second['amount'] > $first['amount']) {
            return -1;
        }

        return 0;
    });

    return $transactions;
}

