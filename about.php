<?php require_once 'config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us - <?php echo SITE_NAME; ?></title>
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
        <div class="container" style="max-width: 800px;">
            <h1 style="margin-bottom: var(--spacing-xl);">About <?php echo SITE_NAME; ?></h1>
            <div class="card" style="padding: var(--spacing-2xl);">
                <p style="font-size: 1.125rem; line-height: 1.8;">
                    <?php echo SITE_NAME; ?> is a modern learning management system designed to provide high-quality education to students worldwide. 
                    Our platform connects expert instructors with eager learners, creating a vibrant community of knowledge sharing.
                </p>
                <p style="font-size: 1.125rem; line-height: 1.8; margin-top: var(--spacing-lg);">
                    We believe in making education accessible, affordable, and engaging for everyone. Our courses are carefully crafted 
                    to ensure you gain practical skills that can be applied in real-world scenarios.
                </p>
            </div>
        </div>
    </section>
    
    <script>lucide.createIcons();</script>
</body>
</html>
