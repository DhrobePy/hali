<?php
require_once '../config/config.php';
requireLogin();
requireRole('instructor');

$user = getCurrentUser();
$pdo = getDBConnection();
$error = '';
$success = '';

// Get categories
$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    
    if (!$title || !$description || !$category_id) {
        $error = 'All fields are required';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO courses (title, description, category_id, instructor_id, price, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'draft', NOW())
        ");
        
        if ($stmt->execute([$title, $description, $category_id, $user['id'], $price])) {
            $courseId = $pdo->lastInsertId();
            $success = 'Course created successfully!';
            redirect("/instructor/edit-course.php?id=$courseId");
        } else {
            $error = 'Failed to create course';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Course - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="/" class="navbar-brand">
                <i data-lucide="plus-circle"></i>
                <span>Create Course</span>
            </a>
        </div>
    </nav>

    <div class="dashboard-container">
        <aside class="sidebar">
            <nav>
                <ul class="sidebar-nav">
                    <li><a href="/instructor/dashboard-enhanced.php"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
                    <li><a href="/instructor/courses.php"><i data-lucide="book-open"></i> My Courses</a></li>
                    <li><a href="/instructor/create-course.php" class="active"><i data-lucide="plus-circle"></i> Create Course</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>Create New Course</h1>
                <p>Start teaching and share your knowledge</p>
            </div>

            <div class="card" style="padding: 2rem; max-width: 600px;">
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Course Title</label>
                        <input type="text" name="title" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-input" rows="5" required></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-input" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Price (৳)</label>
                        <input type="number" name="price" class="form-input" step="0.01" min="0" required>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Create Course</button>
                </form>
            </div>
        </main>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
