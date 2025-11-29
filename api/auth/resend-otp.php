<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

$email = sanitize($_POST['email'] ?? '');
$otp = sanitize($_POST['otp'] ?? '');

if (!$email || !$otp) {
    http_response_code(400);
    echo json_encode(['error' => 'Email and OTP required']);
    exit;
}

$pdo = getDBConnection();

// Check OTP
$stmt = $pdo->prepare("SELECT * FROM otp_tokens WHERE email = ? AND token = ? AND expires_at > NOW() AND used = 0");
$stmt->execute([$email, $otp]);
$token = $stmt->fetch();

if (!$token) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or expired OTP']);
    exit;
}

// Mark as used
$stmt = $pdo->prepare("UPDATE otp_tokens SET used = 1 WHERE id = ?");
$stmt->execute([$token['id']]);

// Verify user
$stmt = $pdo->prepare("UPDATE users SET is_verified = 1 WHERE email = ?");
$stmt->execute([$email]);

echo json_encode(['success' => true, 'message' => 'Email verified successfully']);
EOF1
<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

$email = sanitize($_POST['email'] ?? '');

if (!$email) {
    http_response_code(400);
    echo json_encode(['error' => 'Email required']);
    exit;
}

$pdo = getDBConnection();

// Generate OTP
$otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

// Save OTP
$stmt = $pdo->prepare("
    INSERT INTO otp_tokens (email, token, type, expires_at, created_at)
    VALUES (?, ?, 'email', DATE_ADD(NOW(), INTERVAL 10 MINUTE), NOW())
");
$stmt->execute([$email, $otp]);

// Send email (placeholder)
// sendOTPEmail($email, $otp);

echo json_encode(['success' => true, 'message' => 'OTP sent to email']);
