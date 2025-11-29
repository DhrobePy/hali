<?php
require_once '../config/config.php';
requireLogin();
requireRole('instructor');

$user = getCurrentUser();
$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $slug = sanitize($_POST['slug']);
    $description = sanitize($_POST['description']);
    $categoryId = intval($_POST['category_id']);
    $level = sanitize($_POST['level']);
    $price = floatval($_POST['price']);
    
    $stmt = $pdo->prepare("
        INSERT INTO courses (instructor_id, category_id, title, slug, description, level, price, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'draft', NOW())
    ");
    $stmt->execute([$user['id'], $categoryId, $title, $slug, $description, $level, $price]);
    
    header('Location: /instructor/dashboard.php?message=course_created');
    exit;
}

$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Course - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <h1>Create New Course</h1>
            <form method="POST" class="card" style="max-width: 800px; padding: var(--spacing-2xl);">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label class="form-label">Course Title</label>
                    <input type="text" name="title" required class="form-input" placeholder="Complete Web Development Bootcamp">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Slug (URL)</label>
                    <input type="text" name="slug" required class="form-input" placeholder="complete-web-development-bootcamp">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" required class="form-input" rows="5"></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-lg);">
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category_id" required class="form-input">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Level</label>
                        <select name="level" required class="form-input">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Price (BDT)</label>
                    <input type="number" name="price" required class="form-input" placeholder="2999" step="0.01">
                </div>
                
                <button type="submit" class="btn btn-primary">Create Course</button>
            </form>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
EOF1
<?php
require_once '../config/config.php';
require_once '../includes/Course.php';
requireLogin();
requireRole('instructor');

$user = getCurrentUser();
$courseId = intval($_GET['id'] ?? 0);

$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND instructor_id = ?");
$stmt->execute([$courseId, $user['id']]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: /instructor/dashboard.php');
    exit;
}

$modules = Course::getModulesWithLessons($courseId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Course - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <h1>Edit Course: <?php echo htmlspecialchars($course['title']); ?></h1>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-2xl); margin-top: var(--spacing-2xl);">
                <div class="card" style="padding: var(--spacing-xl);">
                    <h3>Course Modules</h3>
                    <?php foreach ($modules as $module): ?>
                        <div class="module-item">
                            <div class="module-header"><?php echo htmlspecialchars($module['title']); ?></div>
                            <div class="lesson-list">
                                <?php foreach ($module['lessons'] as $lesson): ?>
                                    <div class="lesson-item">
                                        <i data-lucide="play-circle"></i>
                                        <span><?php echo htmlspecialchars($lesson['title']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <a href="/instructor/add-module.php?course_id=<?php echo $courseId; ?>" class="btn btn-primary" style="margin-top: var(--spacing-lg);">Add Module</a>
                </div>
                
                <div class="card" style="padding: var(--spacing-xl);">
                    <h3>Course Settings</h3>
                    <form method="POST" action="/api/courses/update.php">
                        <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-input">
                                <option value="draft" <?php echo $course['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                <option value="published" <?php echo $course['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Update Course</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
