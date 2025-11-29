<?php
require_once '../../config/config.php';
requireLogin();
requireRole('instructor');

$courseId = intval($_POST['course_id'] ?? 0);
$status = sanitize($_POST['status'] ?? 'draft');
$pdo = getDBConnection();

$user = getCurrentUser();

// Verify ownership
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND instructor_id = ?");
$stmt->execute([$courseId, $user['id']]);
if (!$stmt->fetch()) {
    http_response_code(403);
    exit('Unauthorized');
}

$stmt = $pdo->prepare("UPDATE courses SET status = ? WHERE id = ?");
$stmt->execute([$status, $courseId]);

header('Location: /instructor/edit-course.php?id=' . $courseId);
