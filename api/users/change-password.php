<?php
require_once '../../config/config.php';
requireLogin();

header('Content-Type: application/json');

$user = getCurrentUser();
$name = sanitize($_POST['name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$bio = sanitize($_POST['bio'] ?? '');

$pdo = getDBConnection();

$stmt = $pdo->prepare("
    UPDATE users 
    SET name = ?, email = ?, phone = ?, bio = ?, updated_at = NOW()
    WHERE id = ?
");
$stmt->execute([$name, $email, $phone, $bio, $user['id']]);

echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
EOF1
<?php
require_once '../../config/config.php';
requireLogin();

header('Content-Type: application/json');

$user = getCurrentUser();
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($newPassword !== $confirmPassword) {
    http_response_code(400);
    echo json_encode(['error' => 'Passwords do not match']);
    exit;
}

$pdo = getDBConnection();

$stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$result = $stmt->fetch();

if (!password_verify($currentPassword, $result['password_hash'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Current password is incorrect']);
    exit;
}

$newHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

$stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
$stmt->execute([$newHash, $user['id']]);

echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
