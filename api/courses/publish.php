<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

$query = sanitize($_GET['q'] ?? '');

if (strlen($query) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

$pdo = getDBConnection();

$stmt = $pdo->prepare("
    SELECT id, title, description, price FROM courses
    WHERE (title LIKE ? OR description LIKE ?) AND status = 'published'
    LIMIT 10
");
$stmt->execute(['%' . $query . '%', '%' . $query . '%']);
$results = $stmt->fetchAll();

echo json_encode(['results' => $results]);
EOF1
<?php
require_once '../../config/config.php';
requireLogin();
requireRole('instructor');

header('Content-Type: application/json');

$courseId = intval($_POST['course_id'] ?? 0);
$status = sanitize($_POST['status'] ?? 'published');
$user = getCurrentUser();
$pdo = getDBConnection();

// Verify ownership
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND instructor_id = ?");
$stmt->execute([$courseId, $user['id']]);
if (!$stmt->fetch()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$stmt = $pdo->prepare("UPDATE courses SET status = ? WHERE id = ?");
$stmt->execute([$status, $courseId]);

echo json_encode(['success' => true, 'message' => 'Course status updated']);
