<?php
require_once '../config/config.php';
requireLogin();
requireRole('instructor');

$user = getCurrentUser();
$pdo = getDBConnection();
$courseId = intval($_GET['course_id'] ?? 0);
$error = '';
$success = '';

// Verify course ownership
$stmt = $pdo->prepare("SELECT id FROM courses WHERE id = ? AND instructor_id = ?");
$stmt->execute([$courseId, $user['id']]);
if (!$stmt->fetch()) {
    die('Course not found');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    
    if (!$title) {
        $error = 'Module title is required';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO modules (course_id, title, description, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        
        if ($stmt->execute([$courseId, $title, $description])) {
            $success = 'Module added successfully!';
        } else {
            $error = 'Failed to add module';
        }
    }
}

// Get course modules
$stmt = $pdo->prepare("SELECT id, title FROM modules WHERE course_id = ? ORDER BY created_at");
$stmt->execute([$courseId]);
$modules = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Module - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="/" class="navbar-brand">
                <i data-lucide="layers"></i>
                <span>Add Module</span>
            </a>
        </div>
    </nav>

    <div class="dashboard-container">
        <aside class="sidebar">
            <nav>
                <ul class="sidebar-nav">
                    <li><a href="/instructor/dashboard-enhanced.php"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
                    <li><a href="/instructor/courses.php"><i data-lucide="book-open"></i> My Courses</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>Add Module</h1>
                <p>Organize your course content into modules</p>
            </div>

            <div class="card" style="padding: 2rem; max-width: 600px;">
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Module Title</label>
                        <input type="text" name="title" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-input" rows="4"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Add Module</button>
                </form>
            </div>

            <?php if ($modules): ?>
                <div class="card" style="padding: 2rem; margin-top: 2rem;">
                    <h2>Course Modules</h2>
                    <div class="data-table" style="margin-top: 1rem;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($modules as $module): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($module['title']); ?></td>
                                        <td>
                                            <a href="/instructor/add-lesson.php?module_id=<?php echo $module['id']; ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Add Lesson</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
