<?php
require_once '../../config/config.php';
requireLogin();

header('Content-Type: application/json');

$courseId = intval($_POST['course_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 0);
$comment = sanitize($_POST['comment'] ?? '');
$user = getCurrentUser();
$pdo = getDBConnection();

// Verify enrollment
$stmt = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
$stmt->execute([$user['id'], $courseId]);
if (!$stmt->fetch()) {
    http_response_code(403);
    echo json_encode(['error' => 'Not enrolled']);
    exit;
}

// Save review
$stmt = $pdo->prepare("
    INSERT INTO reviews (course_id, user_id, rating, comment, created_at)
    VALUES (?, ?, ?, ?, NOW())
    ON DUPLICATE KEY UPDATE rating = ?, comment = ?
");
$stmt->execute([$courseId, $user['id'], $rating, $comment, $rating, $comment]);

echo json_encode(['success' => true, 'message' => 'Review submitted']);
EOF1
<?php
require_once '../../config/config.php';
requireLogin();

$certId = intval($_GET['id'] ?? 0);
$user = getCurrentUser();
$pdo = getDBConnection();

// Verify ownership
$stmt = $pdo->prepare("SELECT * FROM certificates WHERE id = ? AND user_id = ?");
$stmt->execute([$certId, $user['id']]);
$cert = $stmt->fetch();

if (!$cert) {
    http_response_code(403);
    exit('Unauthorized');
}

// Generate PDF (placeholder - would use FPDF or similar)
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="certificate.pdf"');

echo "Certificate PDF would be generated here";
