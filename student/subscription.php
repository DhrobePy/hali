<?php
require_once '../config/config.php';
require_once '../includes/Course.php';
requireLogin();
requireRole('student');
$user = getCurrentUser();
$enrollments = Course::getUserEnrollments($user['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Courses - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <nav>
                <ul class="sidebar-nav">
                    <li><a href="/student/dashboard.php"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
                    <li><a href="/student/courses.php" class="active"><i data-lucide="book-open"></i> My Courses</a></li>
                    <li><a href="/logout.php"><i data-lucide="log-out"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <h1>My Courses</h1>
            <div class="course-grid">
                <?php foreach ($enrollments as $enrollment): ?>
                    <div class="course-card">
                        <img src="<?php echo htmlspecialchars($enrollment['thumbnail_url'] ?? '/assets/images/placeholder.png'); ?>" alt="" class="course-thumbnail">
                        <div class="course-content">
                            <h3><?php echo htmlspecialchars($enrollment['title']); ?></h3>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $enrollment['progress']; ?>%;"></div>
                            </div>
                            <a href="/course.php?slug=<?php echo urlencode($enrollment['slug']); ?>" class="btn btn-primary">Continue</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
EOF
<?php
require_once '../config/config.php';
requireLogin();
requireRole('student');
$user = getCurrentUser();
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT * FROM subscription_plans ORDER BY price ASC");
$plans = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Subscription - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <nav>
                <ul class="sidebar-nav">
                    <li><a href="/student/dashboard.php"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
                    <li><a href="/student/subscription.php" class="active"><i data-lucide="credit-card"></i> Subscription</a></li>
                    <li><a href="/logout.php"><i data-lucide="log-out"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <h1>Choose Your Plan</h1>
            <div class="pricing-grid">
                <?php foreach ($plans as $plan): ?>
                    <div class="pricing-card">
                        <h3><?php echo htmlspecialchars($plan['name']); ?></h3>
                        <div class="pricing-price"><?php echo formatCurrency($plan['price']); ?></div>
                        <p><?php echo htmlspecialchars($plan['description']); ?></p>
                        <form method="POST" action="/api/payments/process.php">
                            <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                            <input type="hidden" name="amount" value="<?php echo $plan['price']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <select name="payment_method" required class="form-input">
                                <option value="">Select Payment Method</option>
                                <option value="bkash">bKash</option>
                                <option value="nagad">Nagad</option>
                            </select>
                            <button type="submit" class="btn btn-primary">Subscribe</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
