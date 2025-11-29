<?php
require_once '../config/config.php';
requireLogin();
$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $stmt->execute([$name, $email, $user['id']]);
    
    header('Location: /student/profile.php?message=updated');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <nav>
                <ul class="sidebar-nav">
                    <li><a href="/student/dashboard.php"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
                    <li><a href="/student/profile.php" class="active"><i data-lucide="user"></i> Profile</a></li>
                    <li><a href="/logout.php"><i data-lucide="log-out"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <h1>My Profile</h1>
            <form method="POST" class="card" style="max-width: 600px; padding: var(--spacing-2xl);">
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required class="form-input">
                </div>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
