<?php
require_once '../../config/config.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDBConnection();

$stmt = $pdo->prepare("
    SELECT e.*, c.title, c.id as course_id,
    (SELECT COUNT(*) FROM lessons WHERE module_id IN (SELECT id FROM modules WHERE course_id = c.id)) as total_lessons,
    (SELECT COUNT(*) FROM lesson_progress WHERE user_id = ? AND lesson_id IN (SELECT id FROM lessons WHERE module_id IN (SELECT id FROM modules WHERE course_id = c.id))) as completed_lessons
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE e.user_id = ?
    ORDER BY e.enrolled_at DESC
");
$stmt->execute([$user['id'], $user['id']]);
$enrollments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Progress - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <nav>
                <ul class="sidebar-nav">
                    <li><a href="/student/dashboard.php"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
                    <li><a href="/student/progress.php" class="active"><i data-lucide="trending-up"></i> Progress</a></li>
                    <li><a href="/logout.php"><i data-lucide="log-out"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <h1>My Learning Progress</h1>
            
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Progress</th>
                            <th>Lessons Completed</th>
                            <th>Enrolled Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrollments as $enrollment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($enrollment['title']); ?></td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $enrollment['total_lessons'] > 0 ? ($enrollment['completed_lessons'] / $enrollment['total_lessons']) * 100 : 0; ?>%;"></div>
                                    </div>
                                </td>
                                <td><?php echo $enrollment['completed_lessons']; ?> / <?php echo $enrollment['total_lessons']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($enrollment['enrolled_at'])); ?></td>
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
