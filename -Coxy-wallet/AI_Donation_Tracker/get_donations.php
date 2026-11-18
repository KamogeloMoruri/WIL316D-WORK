<?php
/**
 * get_donations.php
 * Returns all donations plus the running total, as JSON, so the page can
 * render the table and summary dynamically instead of using hard-coded
 * example data.
 */

require __DIR__ . '/config.php';
header('Content-Type: application/json');

$rows = $pdo->query(
    'SELECT id, donor_name, amount, purpose, cardano_tx_id, created_at
     FROM donations
     ORDER BY id DESC'
)->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
foreach ($rows as $row) {
    $total += (float) $row['amount'];
}

echo json_encode([
    'donations' => $rows,
    'total'     => $total,
]);
