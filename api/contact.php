<?php
require_once '../config/config.php';

header('Content-Type: application/json');

$name = sanitize($_POST['name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$message = sanitize($_POST['message'] ?? '');

if (!$name || !$email || !$message) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields required']);
    exit;
}

$pdo = getDBConnection();

// Save contact message
$stmt = $pdo->prepare("
    INSERT INTO contact_messages (name, email, message, created_at)
    VALUES (?, ?, ?, NOW())
");
$stmt->execute([$name, $email, $message]);

// Send confirmation email (placeholder)
// sendContactConfirmation($email, $name);

echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
