<?php require_once 'config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Terms of Service - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <nav class="navbar">
        <div class="container navbar-container">
            <a href="/" class="navbar-brand">
                <i data-lucide="book-open"></i>
                <span class="text-gradient"><?php echo SITE_NAME; ?></span>
            </a>
            <div><a href="/login.php" class="btn btn-secondary">Sign In</a></div>
        </div>
    </nav>
    
    <section class="section">
        <div class="container" style="max-width: 900px;">
            <h1 style="margin-bottom: var(--spacing-xl);">Terms of Service</h1>
            <div class="card" style="padding: var(--spacing-2xl);">
                <h2>1. Acceptance of Terms</h2>
                <p>By accessing and using this website, you accept and agree to be bound by the terms and provision of this agreement.</p>
                
                <h2 style="margin-top: var(--spacing-xl);">2. Use License</h2>
                <p>Permission is granted to temporarily download one copy of the materials (information or software) on <?php echo SITE_NAME; ?> for personal, non-commercial transitory viewing only.</p>
                
                <h2 style="margin-top: var(--spacing-xl);">3. Disclaimer</h2>
                <p>The materials on <?php echo SITE_NAME; ?> are provided on an 'as is' basis. <?php echo SITE_NAME; ?> makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties including, without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights.</p>
                
                <h2 style="margin-top: var(--spacing-xl);">4. Limitations</h2>
                <p>In no event shall <?php echo SITE_NAME; ?> or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on <?php echo SITE_NAME; ?>.</p>
            </div>
        </div>
    </section>
    
    <script>lucide.createIcons();</script>
</body>
</html>
