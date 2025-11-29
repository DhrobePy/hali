<?php
require_once '../config/config.php';
requireLogin();
requireRole('instructor');

$user = getCurrentUser();
$pdo = getDBConnection();

// Get instructor's courses with enrollment stats
$stmt = $pdo->prepare("
    SELECT c.*, 
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as total_enrollments,
    (SELECT COUNT(*) FROM lessons WHERE module_id IN (SELECT id FROM modules WHERE course_id = c.id)) as total_lessons,
    (SELECT AVG(rating) FROM reviews WHERE course_id = c.id) as avg_rating
    FROM courses c
    WHERE c.instructor_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$user['id']]);
$courses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Analytics - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <h1>Course Analytics</h1>
            
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Enrollments</th>
                            <th>Lessons</th>
                            <th>Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['title']); ?></td>
                                <td><?php echo $course['total_enrollments']; ?></td>
                                <td><?php echo $course['total_lessons']; ?></td>
                                <td>
                                    <?php if ($course['avg_rating']): ?>
                                        <span style="color: var(--warning);">
                                            <?php echo number_format($course['avg_rating'], 1); ?>/5
                                            <?php echo str_repeat('★', floor($course['avg_rating'])); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">No ratings</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
