<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

// This endpoint receives webhooks from payment gateways
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

$transactionId = $data['transaction_id'] ?? '';
$status = $data['status'] ?? '';

if (!$transactionId || !$status) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$pdo = getDBConnection();

// Update transaction status
$stmt = $pdo->prepare("UPDATE transactions SET status = ? WHERE transaction_id = ?");
$stmt->execute([$status, $transactionId]);

// If payment completed, create subscription
if ($status === 'completed') {
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE transaction_id = ?");
    $stmt->execute([$transactionId]);
    $transaction = $stmt->fetch();
    
    if ($transaction) {
        $stmt = $pdo->prepare("
            INSERT INTO subscriptions (user_id, plan_id, transaction_id, status, starts_at, expires_at, created_at)
            VALUES (?, ?, ?, 'active', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), NOW())
        ");
        $stmt->execute([$transaction['user_id'], $transaction['plan_id'], $transactionId]);
    }
}

echo json_encode(['success' => true]);
