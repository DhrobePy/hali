<?php
require_once '../config/config.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDBConnection();

// Get enrolled courses with progress
$stmt = $pdo->prepare("
    SELECT c.*, 
    (SELECT COUNT(*) FROM lessons WHERE module_id IN (SELECT id FROM modules WHERE course_id = c.id)) as total_lessons,
    (SELECT COUNT(*) FROM lesson_progress WHERE user_id = ? AND lesson_id IN (SELECT id FROM lessons WHERE module_id IN (SELECT id FROM modules WHERE course_id = c.id)) AND completed = 1) as completed_lessons
    FROM courses c
    JOIN enrollments e ON c.id = e.course_id
    WHERE e.user_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$user['id'], $user['id']]);
$courses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="/" class="navbar-brand">
                <i data-lucide="book-open"></i>
                <span>My Courses</span>
            </a>
        </div>
    </nav>

    <div class="dashboard-container">
        <aside class="sidebar">
            <nav>
                <ul class="sidebar-nav">
                    <li><a href="/student/dashboard-enhanced.php"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
                    <li><a href="/student/courses.php" class="active"><i data-lucide="book-open"></i> My Courses</a></li>
                    <li><a href="/student/certificates.php"><i data-lucide="award"></i> Certificates</a></li>
                    <li><a href="/student/progress.php"><i data-lucide="bar-chart-3"></i> Progress</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>My Enrolled Courses</h1>
                <p>Continue learning and complete your courses</p>
            </div>

            <?php if (empty($courses)): ?>
                <div class="card" style="padding: 3rem; text-align: center;">
                    <i data-lucide="inbox" style="width: 64px; height: 64px; margin: 0 auto 1rem; color: var(--text-secondary);"></i>
                    <h2>No Courses Yet</h2>
                    <p style="color: var(--text-secondary);">You haven't enrolled in any courses yet.</p>
                    <a href="/courses.php" class="btn btn-primary" style="margin-top: 1rem;">Browse Courses</a>
                </div>
            <?php else: ?>
                <div class="course-grid">
                    <?php foreach ($courses as $course): ?>
                        <div class="course-card">
                            <div class="course-card-header">
                                <i data-lucide="book-open" style="width: 64px; height: 64px;"></i>
                            </div>
                            <div class="course-card-body">
                                <h3 class="course-card-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                                <p class="course-card-description"><?php echo htmlspecialchars(substr($course['description'], 0, 80)); ?>...</p>
                                <div style="margin-top: auto;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                        <span style="font-size: 0.85rem; color: var(--text-secondary);">Progress</span>
                                        <span style="font-weight: 600; color: var(--primary);">
                                            <?php echo $course['total_lessons'] > 0 ? round(($course['completed_lessons'] / $course['total_lessons']) * 100) : 0; ?>%
                                        </span>
                                    </div>
                                    <div style="width: 100%; height: 6px; background: var(--bg-tertiary); border-radius: 3px; overflow: hidden;">
                                        <div style="width: <?php echo $course['total_lessons'] > 0 ? round(($course['completed_lessons'] / $course['total_lessons']) * 100) : 0; ?>%; height: 100%; background: var(--gradient-primary);"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="course-card-footer">
                                <a href="/student/learn.php?course_id=<?php echo $course['id']; ?>" class="btn btn-primary" style="flex: 1; justify-content: center;">
                                    Continue
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
