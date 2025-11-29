<?php require_once 'config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Privacy Policy - <?php echo SITE_NAME; ?></title>
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
            <h1 style="margin-bottom: var(--spacing-xl);">Privacy Policy</h1>
            <div class="card" style="padding: var(--spacing-2xl);">
                <h2>1. Information We Collect</h2>
                <p>We collect information you provide directly to us, such as when you create an account, make a purchase, or contact us for support.</p>
                
                <h2 style="margin-top: var(--spacing-xl);">2. How We Use Your Information</h2>
                <p>We use the information we collect to provide, maintain, and improve our services, process transactions, and send you technical notices and support messages.</p>
                
                <h2 style="margin-top: var(--spacing-xl);">3. Data Security</h2>
                <p>We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.</p>
                
                <h2 style="margin-top: var(--spacing-xl);">4. Contact Us</h2>
                <p>If you have questions about this privacy policy, please contact us at support@fajracct.com</p>
            </div>
        </div>
    </section>
    
    <script>lucide.createIcons();</script>
</body>
</html>
