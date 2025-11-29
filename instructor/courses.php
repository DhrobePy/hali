<?php
require_once '../config/config.php';
requireLogin();
requireRole('instructor');

$user = getCurrentUser();
$pdo = getDBConnection();
$stmt = $pdo->prepare("
    SELECT c.*, 
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrollment_count
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
    <title>My Courses - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <div style="display: flex; justify-content: space-between; margin-bottom: var(--spacing-2xl);">
                <h1>My Courses</h1>
                <a href="/instructor/create-course.php" class="btn btn-primary">
                    <i data-lucide="plus"></i> Create Course
                </a>
            </div>
            
            <div class="course-grid">
                <?php foreach ($courses as $course): ?>
                    <div class="course-card">
                        <img src="<?php echo htmlspecialchars($course['thumbnail_url'] ?? '/assets/images/placeholder.png'); ?>" alt="" class="course-thumbnail">
                        <div class="course-content">
                            <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                            <p class="text-muted">
                                <?php echo $course['enrollment_count']; ?> students • 
                                <span class="course-badge" style="background: <?php echo $course['status'] === 'published' ? 'var(--success)' : 'var(--warning)'; ?>; color: white;">
                                    <?php echo ucfirst($course['status']); ?>
                                </span>
                            </p>
                            <a href="/instructor/edit-course.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">Edit Course</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
