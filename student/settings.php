<?php
require_once '../../config/config.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $result = $stmt->fetch();
        
        if (!password_verify($currentPassword, $result['password_hash'])) {
            $error = 'Current password is incorrect';
        } else {
            $newHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$newHash, $user['id']]);
            $success = 'Password changed successfully';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <nav>
                <ul class="sidebar-nav">
                    <li><a href="/student/dashboard.php"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
                    <li><a href="/student/settings.php" class="active"><i data-lucide="settings"></i> Settings</a></li>
                    <li><a href="/logout.php"><i data-lucide="log-out"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <h1>Account Settings</h1>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card" style="max-width: 600px; padding: var(--spacing-2xl);">
                <h3 style="margin-bottom: var(--spacing-lg);">Change Password</h3>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" required class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" required class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" required class="form-input">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
