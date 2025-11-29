<?php
require_once '../config/config.php';
requireLogin();
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siteName = sanitize($_POST['site_name']);
    $siteUrl = sanitize($_POST['site_url']);
    $supportEmail = sanitize($_POST['support_email']);
    
    // In production, save these to database
    // For now, just show success
    header('Location: /admin/settings.php?message=saved');
    exit;
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
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <h1>Site Settings</h1>
            
            <div class="card" style="max-width: 600px; padding: var(--spacing-2xl);">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Site Name</label>
                        <input type="text" name="site_name" value="<?php echo SITE_NAME; ?>" required class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Site URL</label>
                        <input type="url" name="site_url" value="<?php echo SITE_URL; ?>" required class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Support Email</label>
                        <input type="email" name="support_email" value="support@fajracct.com" required class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">bKash API Key</label>
                        <input type="password" name="bkash_api_key" class="form-input" placeholder="Enter your bKash API key">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nagad API Key</label>
                        <input type="password" name="nagad_api_key" class="form-input" placeholder="Enter your Nagad API key">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </form>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
