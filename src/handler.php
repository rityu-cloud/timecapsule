<?php

declare(strict_types=1);

function handleTransactionForm(array &$transactions): ?string
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return null;
    }

    $newTransaction = [
        'id' => (int)($_POST['id'] ?? 0),
        'date' => (string)($_POST['date'] ?? ''),
        'amount' => (float)($_POST['amount'] ?? 0),
        'description' => trim((string)($_POST['description'] ?? '')),
        'merchant' => trim((string)($_POST['merchant'] ?? '')),
    ];

    return addTransaction($transactions, $newTransaction);
}