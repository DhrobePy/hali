<?php
require_once '../../config/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}

$user = getCurrentUser();
$amount = floatval($_POST['amount'] ?? 0);
$paymentMethod = $_POST['payment_method'] ?? '';

if ($amount <= 0 || empty($paymentMethod)) {
    die('Invalid payment details');
}

// Create payment record
$pdo = getDBConnection();
$stmt = $pdo->prepare("
    INSERT INTO payments (user_id, amount, payment_method, status, transaction_id, created_at)
    VALUES (?, ?, ?, 'pending', ?, NOW())
");
$transactionId = 'TXN' . time() . rand(1000, 9999);
$stmt->execute([$user['id'], $amount, $paymentMethod, $transactionId]);

// Redirect to payment gateway
if ($paymentMethod === 'bkash') {
    header('Location: /api/payments/bkash.php?txn=' . $transactionId);
} elseif ($paymentMethod === 'nagad') {
    header('Location: /api/payments/nagad.php?txn=' . $transactionId);
} else {
    header('Location: /student/subscription.php?error=invalid_method');
}
exit;
