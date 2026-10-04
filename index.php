<?php

declare(strict_types=1);

require_once __DIR__ . '/src/functions.php';
require_once __DIR__ . '/src/handler.php';

$dataFile = __DIR__ . '/data.json';
$transactions = loadTransactions($dataFile);
$message = handleTransactionForm($transactions, $dataFile);

$preparedTransactions = [];
foreach ($transactions as $transaction) {
    $transaction['days'] = daysSinceTransaction($transaction['date']);
    $preparedTransactions[] = $transaction;
}

$totalAmount = calculateTotalAmount($transactions);
$foundTransactions = findTransactionByDescription($transactions, 'Kupili');
$transactionById = findTransactionById($transactions, 4);
$transactionsByDate = sortTransactionsByDate($transactions);
$transactionsByAmount = sortTransactionsByAmount($transactions);

$title = 'Native PHP Transactions';
require __DIR__ . '/templates/page.php';
