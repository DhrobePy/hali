<?php
require_once '../../config/config.php';
requireLogin();

header('Content-Type: application/json');

$transactionId = sanitize($_GET['transaction_id'] ?? '');

if (!$transactionId) {
    http_response_code(400);
    echo json_encode(['error' => 'Transaction ID required']);
    exit;
}

$pdo = getDBConnection();

$stmt = $pdo->prepare("SELECT * FROM transactions WHERE transaction_id = ?");
$stmt->execute([$transactionId]);
$transaction = $stmt->fetch();

if (!$transaction) {
    http_response_code(404);
    echo json_encode(['error' => 'Transaction not found']);
    exit;
}

// In production, verify with payment gateway API
$verified = true;

if ($verified && $transaction['status'] === 'pending') {
    $stmt = $pdo->prepare("UPDATE transactions SET status = 'completed' WHERE transaction_id = ?");
    $stmt->execute([$transactionId]);
}

echo json_encode([
    'success' => true,
    'transaction' => $transaction,
    'verified' => $verified
]);
