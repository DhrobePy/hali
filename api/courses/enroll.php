<?php
require_once '../../config/config.php';
require_once '../../includes/Course.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    die('Invalid request');
}

$user = getCurrentUser();
$courseId = intval($_POST['course_id'] ?? 0);

if ($courseId <= 0) {
    header('Location: /');
    exit;
}

// Check if already enrolled
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
$stmt->execute([$user['id'], $courseId]);

if ($stmt->fetch()) {
    header('Location: /student/dashboard.php?message=already_enrolled');
    exit;
}

// Enroll user
$stmt = $pdo->prepare("
    INSERT INTO enrollments (user_id, course_id, enrolled_at, status, progress)
    VALUES (?, ?, NOW(), 'active', 0)
");
$stmt->execute([$user['id'], $courseId]);

// Update enrollment count
$stmt = $pdo->prepare("UPDATE courses SET enrollment_count = enrollment_count + 1 WHERE id = ?");
$stmt->execute([$courseId]);

header('Location: /student/dashboard.php?message=enrolled_success');
exit;
