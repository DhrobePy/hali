<?php
require_once '../config/config.php';
requireLogin();
requireRole('admin');

$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $name = sanitize($_POST['name']);
        $slug = sanitize($_POST['slug']);
        $description = sanitize($_POST['description']);
        
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
        $stmt->execute([$name, $slug, $description]);
        header('Location: /admin/categories.php?message=added');
        exit;
    } elseif (isset($_POST['delete_category'])) {
        $id = intval($_POST['category_id']);
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: /admin/categories.php?message=deleted');
        exit;
    }
}

$stmt = $pdo->query("
    SELECT c.*, 
    (SELECT COUNT(*) FROM courses WHERE category_id = c.id) as course_count
    FROM categories c 
    ORDER BY c.name ASC
");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-2xl);">
                <div>
                    <h1>Manage Categories</h1>
                    <p class="text-muted">Total: <?php echo count($categories); ?> categories</p>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 400px; gap: var(--spacing-2xl);">
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Courses</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                    <td><?php echo htmlspecialchars($category['slug']); ?></td>
                                    <td><?php echo $category['course_count']; ?></td>
                                    <td>
                                        <form method="POST" onsubmit="return confirm('Delete this category?');" style="display: inline;">
                                            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <button type="submit" name="delete_category" class="btn" style="padding: var(--spacing-sm); background: var(--error); color: white;">
                                                <i data-lucide="trash" style="width: 16px; height: 16px;"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="card" style="padding: var(--spacing-xl); height: fit-content;">
                    <h3 style="margin-bottom: var(--spacing-lg);">Add Category</h3>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <div class="form-group">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" required class="form-input" placeholder="Web Development">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" required class="form-input" placeholder="web-development">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-input" rows="3"></textarea>
                        </div>
                        <button type="submit" name="add_category" class="btn btn-primary" style="width: 100%;">Add Category</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
