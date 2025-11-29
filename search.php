<?php
require_once 'config/config.php';

$query = sanitize($_GET['q'] ?? '');
$pdo = getDBConnection();

if ($query) {
    $stmt = $pdo->prepare("
        SELECT id, title, description, price FROM courses
        WHERE (title LIKE ? OR description LIKE ?) AND status = 'published'
    ");
    $stmt->execute(['%' . $query . '%', '%' . $query . '%']);
    $results = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results - <?php echo SITE_NAME; ?></title>
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
            <form method="GET" style="flex: 1; max-width: 400px; margin: 0 var(--spacing-lg);">
                <input type="text" name="q" value="<?php echo htmlspecialchars($query); ?>" placeholder="Search courses..." class="form-input" style="margin: 0;">
            </form>
            <div><a href="/login.php" class="btn btn-secondary">Sign In</a></div>
        </div>
    </nav>
    
    <section class="section">
        <div class="container">
            <h1 style="margin-bottom: var(--spacing-xl);">Search Results for "<?php echo htmlspecialchars($query); ?>"</h1>
            
            <?php if (isset($results) && count($results) > 0): ?>
                <div class="course-grid">
                    <?php foreach ($results as $course): ?>
                        <div class="card">
                            <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                            <p class="text-muted"><?php echo htmlspecialchars(substr($course['description'], 0, 100)); ?>...</p>
                            <p style="margin-top: var(--spacing-lg); font-weight: 600; color: var(--primary);">
                                <?php echo formatCurrency($course['price']); ?>
                            </p>
                            <a href="/course.php?id=<?php echo $course['id']; ?>" class="btn btn-primary" style="margin-top: var(--spacing-lg);">View Course</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card" style="padding: var(--spacing-2xl); text-align: center;">
                    <i data-lucide="search" style="width: 64px; height: 64px; color: var(--muted-foreground); margin: 0 auto var(--spacing-lg);"></i>
                    <p class="text-muted">No courses found matching your search.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <script>lucide.createIcons();</script>
</body>
</html>
