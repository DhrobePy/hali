<?php
require_once '../../config/config.php';
requireLogin();

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$lessonId = intval($data['lesson_id'] ?? 0);
$courseId = intval($data['course_id'] ?? 0);
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

// Mark as complete
$stmt = $pdo->prepare("
    INSERT IGNORE INTO lesson_progress (user_id, lesson_id, completed_at)
    VALUES (?, ?, NOW())
");
$stmt->execute([$user['id'], $lessonId]);

echo json_encode(['success' => true]);
