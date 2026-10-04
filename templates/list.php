<section>
    <h2>Spisok tranzakcii</h2>

    <table>
        <tr>
            <th>id</th>
            <th>date</th>
            <th>amount</th>
            <th>description</th>
            <th>merchant</th>
            <th>days</th>
        </tr>

        <?php foreach ($transactions as $transaction) { ?>
            <tr>
                <td><?php echo htmlspecialchars((string)$transaction['id']); ?></td>
                <td><?php echo htmlspecialchars($transaction['date']); ?></td>
                <td><?php echo number_format((float)$transaction['amount'], 2); ?></td>
                <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                <td><?php echo htmlspecialchars($transaction['merchant']); ?></td>
                <td><?php echo daysSinceTransaction($transaction['date']); ?></td>
            </tr>
        <?php } ?>
        <tr>
            <th colspan="6">Total Amount: <?php echo number_format($totalAmount, 2); ?></th>
        </tr>
    </table>
</section>

<section>
    <h2>findTransactionByDescription: Kupili</h2>

    <table>
        <tr>
            <th>id</th>
            <th>description</th>
        </tr>

        <?php foreach ($foundTransactions as $transaction) { ?>
            <tr>
                <td><?php echo htmlspecialchars((string)$transaction['id']); ?></td>
                <td><?php echo htmlspecialchars($transaction['description']); ?></td>
            </tr>
        <?php } ?>
    </table>
</section>

<section>
    <h2>findTransactionById primer: 4</h2>
    <?php if ($transactionById !== null) { ?>
        <p>
            Tranzakciya s id 4:
            <?php echo htmlspecialchars($transactionById['description']); ?>,
            stoimost <?php echo number_format((float)$transactionById['amount'], 2); ?>
        </p>
    <?php } ?>
</section>

<section>
    <h2>Sortirovka po date</h2>
    <table>
        <tr>
            <th>id</th>
            <th>date</th>
            <th>amount</th>
            <th>description</th>
            <th>merchant</th>
        </tr>

        <?php foreach ($transactionsByDate as $transaction) { ?>
            <tr>
                <td><?php echo htmlspecialchars((string)$transaction['id']); ?></td>
                <td><?php echo htmlspecialchars($transaction['date']); ?></td>
                <td><?php echo number_format((float)$transaction['amount'], 2); ?></td>
                <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                <td><?php echo htmlspecialchars($transaction['merchant']); ?></td>
            </tr>
        <?php } ?>
    </table>
</section>

<section>
    <h2>Sortirovka po summe</h2>
    <table>
        <tr>
            <th>id</th>
            <th>date</th>
            <th>amount</th>
            <th>description</th>
            <th>merchant</th>
        </tr>

        <?php foreach ($transactionsByAmount as $transaction) { ?>
            <tr>
                <td><?php echo htmlspecialchars((string)$transaction['id']); ?></td>
                <td><?php echo htmlspecialchars($transaction['date']); ?></td>
                <td><?php echo number_format((float)$transaction['amount'], 2); ?></td>
                <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                <td><?php echo htmlspecialchars($transaction['merchant']); ?></td>
            </tr>
        <?php } ?>
    </table>
</section>

