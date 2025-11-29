<?php
require_once '../config/config.php';
requireLogin();
requireRole('instructor');

$user = getCurrentUser();
$courseId = intval($_GET['course_id'] ?? 0);
$pdo = getDBConnection();

// Verify course ownership
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND instructor_id = ?");
$stmt->execute([$courseId, $user['id']]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: /instructor/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $order = intval($_POST['order']);
    
    $stmt = $pdo->prepare("INSERT INTO modules (course_id, title, description, module_order, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$courseId, $title, $description, $order]);
    
    header('Location: /instructor/edit-course.php?id=' . $courseId);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Module - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <h1>Add Module to: <?php echo htmlspecialchars($course['title']); ?></h1>
            
            <div class="card" style="max-width: 600px; padding: var(--spacing-2xl);">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Module Title</label>
                        <input type="text" name="title" required class="form-input" placeholder="Module 1: Introduction">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-input" rows="4"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Order</label>
                        <input type="number" name="order" required class="form-input" placeholder="1" value="1">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Add Module</button>
                </form>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
EOF1
<?php
require_once '../config/config.php';
requireLogin();
requireRole('instructor');

$user = getCurrentUser();
$moduleId = intval($_GET['module_id'] ?? 0);
$pdo = getDBConnection();

// Verify module ownership through course
$stmt = $pdo->prepare("
    SELECT m.* FROM modules m
    JOIN courses c ON m.course_id = c.id
    WHERE m.id = ? AND c.instructor_id = ?
");
$stmt->execute([$moduleId, $user['id']]);
$module = $stmt->fetch();

if (!$module) {
    header('Location: /instructor/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $videoUrl = sanitize($_POST['video_url']);
    $order = intval($_POST['order']);
    
    $stmt = $pdo->prepare("
        INSERT INTO lessons (module_id, title, description, video_url, lesson_order, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$moduleId, $title, $description, $videoUrl, $order]);
    
    header('Location: /instructor/edit-course.php?id=' . $module['course_id']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Lesson - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <h1>Add Lesson to: <?php echo htmlspecialchars($module['title']); ?></h1>
            
            <div class="card" style="max-width: 600px; padding: var(--spacing-2xl);">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Lesson Title</label>
                        <input type="text" name="title" required class="form-input" placeholder="Lesson 1: Getting Started">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-input" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Video URL (Vimeo/Bunny.net)</label>
                        <input type="url" name="video_url" required class="form-input" placeholder="https://vimeo.com/...">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Order</label>
                        <input type="number" name="order" required class="form-input" placeholder="1" value="1">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Add Lesson</button>
                </form>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
