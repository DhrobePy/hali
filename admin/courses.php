<?php
require_once '../config/config.php';
requireLogin();
requireRole('admin');

$pdo = getDBConnection();
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $userId = intval($_POST['user_id']);
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    $stmt->execute([$userId]);
    header('Location: /admin/users.php?message=deleted');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-2xl);">
                <div>
                    <h1>Manage Users</h1>
                    <p class="text-muted">Total: <?php echo count($users); ?> users</p>
                </div>
                <a href="/admin/add-user.php" class="btn btn-primary">
                    <i data-lucide="plus"></i> Add User
                </a>
            </div>
            
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="course-badge" style="background: <?php echo $user['role'] === 'admin' ? 'var(--error)' : ($user['role'] === 'instructor' ? 'var(--primary)' : 'var(--success)'); ?>; color: white;">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="course-badge" style="background: <?php echo $user['is_verified'] ? 'var(--success)' : 'var(--warning)'; ?>; color: white;">
                                        <?php echo $user['is_verified'] ? 'Verified' : 'Pending'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div style="display: flex; gap: var(--spacing-sm);">
                                        <a href="/admin/edit-user.php?id=<?php echo $user['id']; ?>" class="btn btn-secondary" style="padding: var(--spacing-sm);">
                                            <i data-lucide="edit" style="width: 16px; height: 16px;"></i>
                                        </a>
                                        <?php if ($user['role'] !== 'admin'): ?>
                                            <form method="POST" onsubmit="return confirm('Delete this user?');" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <button type="submit" name="delete_user" class="btn" style="padding: var(--spacing-sm); background: var(--error); color: white;">
                                                    <i data-lucide="trash" style="width: 16px; height: 16px;"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
EOFUSERS
<?php
require_once '../config/config.php';
requireLogin();
requireRole('admin');

$pdo = getDBConnection();
$stmt = $pdo->query("
    SELECT c.*, u.name as instructor_name, 
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrollment_count
    FROM courses c 
    JOIN users u ON c.instructor_id = u.id 
    ORDER BY c.created_at DESC
");
$courses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-2xl);">
                <div>
                    <h1>Manage Courses</h1>
                    <p class="text-muted">Total: <?php echo count($courses); ?> courses</p>
                </div>
            </div>
            
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Instructor</th>
                            <th>Level</th>
                            <th>Price</th>
                            <th>Enrollments</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?php echo $course['id']; ?></td>
                                <td><?php echo htmlspecialchars($course['title']); ?></td>
                                <td><?php echo htmlspecialchars($course['instructor_name']); ?></td>
                                <td><?php echo ucfirst($course['level']); ?></td>
                                <td><?php echo formatCurrency($course['price']); ?></td>
                                <td><?php echo $course['enrollment_count']; ?></td>
                                <td>
                                    <span class="course-badge" style="background: <?php echo $course['status'] === 'published' ? 'var(--success)' : 'var(--warning)'; ?>; color: white;">
                                        <?php echo ucfirst($course['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/course.php?slug=<?php echo urlencode($course['slug']); ?>" class="btn btn-secondary" style="padding: var(--spacing-sm);">
                                        <i data-lucide="eye" style="width: 16px; height: 16px;"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
