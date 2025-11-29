<?php
require_once '../config/config.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDBConnection();

// Get student statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM enrollments WHERE user_id = ?");
$stmt->execute([$user['id']]);
$totalCourses = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM lesson_progress WHERE user_id = ? AND completed = 1");
$stmt->execute([$user['id']]);
$lessonsCompleted = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM quiz_attempts WHERE user_id = ? AND passed = 1");
$stmt->execute([$user['id']]);
$quizzesPassed = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM certificates WHERE user_id = ?");
$stmt->execute([$user['id']]);
$certificatesEarned = $stmt->fetch()['count'];

// Get enrolled courses
$stmt = $pdo->prepare("
    SELECT c.*, 
    (SELECT COUNT(*) FROM lessons WHERE module_id IN (SELECT id FROM modules WHERE course_id = c.id)) as total_lessons,
    (SELECT COUNT(*) FROM lesson_progress WHERE user_id = ? AND lesson_id IN (SELECT id FROM lessons WHERE module_id IN (SELECT id FROM modules WHERE course_id = c.id)) AND completed = 1) as completed_lessons
    FROM courses c
    JOIN enrollments e ON c.id = e.course_id
    WHERE e.user_id = ?
    LIMIT 6
");
$stmt->execute([$user['id'], $user['id']]);
$enrolledCourses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style-enhanced.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="/" class="navbar-brand">
                <i data-lucide="graduation-cap"></i>
                <span><?php echo SITE_NAME; ?></span>
            </a>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <span style="color: var(--text-secondary);"><?php echo htmlspecialchars($user['name']); ?></span>
                <a href="/logout.php" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                    <i data-lucide="log-out" style="width: 16px; height: 16px;"></i>
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <aside class="sidebar">
            <nav>
                <ul class="sidebar-nav">
                    <li><a href="/student/dashboard-enhanced.php" class="active"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
                    <li><a href="/student/courses.php"><i data-lucide="book-open"></i> My Courses</a></li>
                    <li><a href="/student/certificates.php"><i data-lucide="award"></i> Certificates</a></li>
                    <li><a href="/student/progress.php"><i data-lucide="bar-chart-3"></i> Progress</a></li>
                    <li><a href="/student/subscription.php"><i data-lucide="credit-card"></i> Subscription</a></li>
                    <li><a href="/student/profile.php"><i data-lucide="user"></i> Profile</a></li>
                    <li><a href="/student/settings.php"><i data-lucide="settings"></i> Settings</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h1>
                <p>Continue your learning journey and achieve your goals.</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon">
                        <i data-lucide="book-open"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Enrolled Courses</h3>
                        <p class="stat-value"><?php echo $totalCourses; ?></p>
                    </div>
                </div>

                <div class="stat-card secondary">
                    <div class="stat-icon">
                        <i data-lucide="check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Lessons Completed</h3>
                        <p class="stat-value"><?php echo $lessonsCompleted; ?></p>
                    </div>
                </div>

                <div class="stat-card success">
                    <div class="stat-icon">
                        <i data-lucide="zap"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Quizzes Passed</h3>
                        <p class="stat-value"><?php echo $quizzesPassed; ?></p>
                    </div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-icon">
                        <i data-lucide="award"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Certificates Earned</h3>
                        <p class="stat-value"><?php echo $certificatesEarned; ?></p>
                    </div>
                </div>
            </div>

            <!-- Enrolled Courses -->
            <div style="margin-top: 2rem;">
                <h2 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="book-open" style="color: var(--primary);"></i>
                    Continue Learning
                </h2>
                <div class="course-grid">
                    <?php foreach ($enrolledCourses as $course): ?>
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
            </div>
        </main>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
