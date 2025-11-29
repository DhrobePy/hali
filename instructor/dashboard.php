<?php
require_once '../config/config.php';
requireLogin();
requireRole('instructor');
$user = getCurrentUser();
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM courses WHERE instructor_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$courses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Instructor Panel</h2>
            <nav>
                <ul class="sidebar-nav">
                    <li><a href="/instructor/dashboard.php" class="active"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
                    <li><a href="/instructor/courses.php"><i data-lucide="book-open"></i> My Courses</a></li>
                    <li><a href="/instructor/create-course.php"><i data-lucide="plus"></i> Create Course</a></li>
                    <li><a href="/logout.php"><i data-lucide="log-out"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?></h1>
            <div class="course-grid">
                <?php foreach ($courses as $course): ?>
                    <div class="course-card">
                        <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                        <p>Status: <?php echo $course['status']; ?></p>
                        <a href="/instructor/edit-course.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">Edit</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
