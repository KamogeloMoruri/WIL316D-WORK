<?php
/**
 * save_donation.php
 * Receives a POST request from the "Add a donation" form and stores it.
 */

require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Only POST requests are allowed.';
    exit;
}

$donorName = trim($_POST['donorName'] ?? '');
$amount    = $_POST['amount'] ?? '';
$purpose   = trim($_POST['purpose'] ?? '');
$cardanoTx = trim($_POST['cardanoTxId'] ?? '');

if ($donorName === '' || $amount === '' || $purpose === '') {
    http_response_code(400);
    echo 'Please fill in all fields.';
    exit;
}

if (!is_numeric($amount) || (float) $amount <= 0) {
    http_response_code(400);
    echo 'Donation amount must be a positive number.';
    exit;
}

$stmt = $pdo->prepare(
    'INSERT INTO donations (donor_name, amount, purpose, cardano_tx_id) VALUES (:donor_name, :amount, :purpose, :cardano_tx_id)'
);

$stmt->execute([
    ':donor_name'    => $donorName,
    ':amount'        => (float) $amount,
    ':purpose'       => $purpose,
    ':cardano_tx_id' => $cardanoTx !== '' ? $cardanoTx : null,
]);

echo 'Donation saved. Thank you, ' . htmlspecialchars($donorName, ENT_QUOTES) . '!';
