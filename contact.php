<?php require_once 'config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Us - <?php echo SITE_NAME; ?></title>
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
        <div class="container" style="max-width: 600px;">
            <h1 style="margin-bottom: var(--spacing-xl);">Contact Us</h1>
            <div class="card" style="padding: var(--spacing-2xl);">
                <form method="POST" action="/api/contact.php">
                    <div class="form-group">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" required class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" required class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Message</label>
                        <textarea name="message" required class="form-input" rows="5"></textarea>
                    </div>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>
        </div>
    </section>
    
    <script>lucide.createIcons();</script>
</body>
</html>
