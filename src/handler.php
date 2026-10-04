<?php

declare(strict_types=1);

function handleTransactionForm(array &$transactions, string $dataFile): ?string
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return null;
    }

    $newTransaction = [
        'id' => (int)$_POST['id'],
        'date' => $_POST['date'],
        'amount' => (float)$_POST['amount'],
        'description' => trim((string)$_POST['description']),
        'merchant' => trim((string)$_POST['merchant']),
    ];

    $message = addTransaction($transactions, $newTransaction);
    saveTransactions($dataFile, $transactions);

    return $message;
}
