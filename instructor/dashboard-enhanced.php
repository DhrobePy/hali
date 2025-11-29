<?php
require_once '../config/config.php';
requireLogin();
requireRole('instructor');

$user = getCurrentUser();
$pdo = getDBConnection();

// Get instructor statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM courses WHERE instructor_id = ?");
$stmt->execute([$user['id']]);
$totalCourses = $stmt->fetch()['count'];

$stmt = $pdo->prepare("
    SELECT COUNT(*) as count FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE c.instructor_id = ?
");
$stmt->execute([$user['id']]);
$totalStudents = $stmt->fetch()['count'];

$stmt = $pdo->prepare("
    SELECT COUNT(*) as count FROM lessons l
    JOIN modules m ON l.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE c.instructor_id = ?
");
$stmt->execute([$user['id']]);
$totalLessons = $stmt->fetch()['count'];

$stmt = $pdo->prepare("
    SELECT AVG(r.rating) as avg_rating FROM reviews r
    JOIN courses c ON r.course_id = c.id
    WHERE c.instructor_id = ?
");
$stmt->execute([$user['id']]);
$avgRating = $stmt->fetch()['avg_rating'] ?? 0;

// Get instructor's courses
$stmt = $pdo->prepare("
    SELECT c.*, 
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as student_count,
    (SELECT AVG(rating) FROM reviews WHERE course_id = c.id) as avg_rating
    FROM courses c
    WHERE c.instructor_id = ?
    ORDER BY c.created_at DESC
    LIMIT 6
");
$stmt->execute([$user['id']]);
$courses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style-enhanced.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="/" class="navbar-brand">
                <i data-lucide="book-marked"></i>
                <span><?php echo SITE_NAME; ?> Instructor</span>
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
                    <li><a href="/instructor/dashboard-enhanced.php" class="active"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
                    <li><a href="/instructor/courses.php"><i data-lucide="book-open"></i> My Courses</a></li>
                    <li><a href="/instructor/create-course.php"><i data-lucide="plus-circle"></i> Create Course</a></li>
                    <li><a href="/instructor/students.php"><i data-lucide="users"></i> Students</a></li>
                    <li><a href="/instructor/analytics.php"><i data-lucide="bar-chart-3"></i> Analytics</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Instructor Dashboard</h1>
                <p>Manage your courses and track student progress.</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon">
                        <i data-lucide="book-open"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Active Courses</h3>
                        <p class="stat-value"><?php echo $totalCourses; ?></p>
                    </div>
                </div>

                <div class="stat-card secondary">
                    <div class="stat-icon">
                        <i data-lucide="users"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total Students</h3>
                        <p class="stat-value"><?php echo $totalStudents; ?></p>
                    </div>
                </div>

                <div class="stat-card success">
                    <div class="stat-icon">
                        <i data-lucide="layers"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total Lessons</h3>
                        <p class="stat-value"><?php echo $totalLessons; ?></p>
                    </div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-icon">
                        <i data-lucide="star"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Avg Rating</h3>
                        <p class="stat-value"><?php echo number_format($avgRating, 1); ?>/5</p>
                    </div>
                </div>
            </div>

            <!-- My Courses -->
            <div style="margin-top: 2rem;">
                <h2 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="book-open" style="color: var(--primary);"></i>
                    Your Courses
                </h2>
                <div class="course-grid">
                    <?php foreach ($courses as $course): ?>
                        <div class="course-card">
                            <div class="course-card-header">
                                <i data-lucide="book-open" style="width: 64px; height: 64px;"></i>
                            </div>
                            <div class="course-card-body">
                                <h3 class="course-card-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                                <p class="course-card-description"><?php echo htmlspecialchars(substr($course['description'], 0, 80)); ?>...</p>
                                <div style="margin-top: auto; display: flex; gap: 1rem; font-size: 0.9rem; color: var(--text-secondary);">
                                    <span><i data-lucide="users" style="width: 16px; height: 16px; display: inline;"></i> <?php echo $course['student_count']; ?> students</span>
                                    <span><i data-lucide="star" style="width: 16px; height: 16px; display: inline;"></i> <?php echo $course['avg_rating'] ? number_format($course['avg_rating'], 1) : 'No rating'; ?></span>
                                </div>
                            </div>
                            <div class="course-card-footer">
                                <a href="/instructor/edit-course.php?id=<?php echo $course['id']; ?>" class="btn btn-primary" style="flex: 1; justify-content: center;">
                                    Edit
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
