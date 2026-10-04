<?php

declare(strict_types=1);

require_once __DIR__ . '/src/functions.php';
require_once __DIR__ . '/src/handler.php';
require_once __DIR__ . '/src/twig_loader.php';

$dataFile = __DIR__ . '/data.json';
$transactions = loadTransactions($dataFile);
$message = handleTransactionForm($transactions, $dataFile);

$preparedTransactions = [];
foreach ($transactions as $transaction) {
    $transaction['days'] = daysSinceTransaction($transaction['date']);
    $preparedTransactions[] = $transaction;
}

$loader = new Twig\Loader\FilesystemLoader(__DIR__ . '/twig_templates');
$twig = new Twig\Environment($loader, [
    'cache' => false,
    'debug' => true,
    'autoescape' => 'html',
]);

echo $twig->render('page.twig', [
    'message' => $message,
    'transactions' => $preparedTransactions,
    'totalAmount' => calculateTotalAmount($transactions),
    'foundTransactions' => findTransactionByDescription($transactions, 'Kupili'),
    'transactionById' => findTransactionById($transactions, 4),
    'transactionsByDate' => sortTransactionsByDate($transactions),
    'transactionsByAmount' => sortTransactionsByAmount($transactions),
]);
