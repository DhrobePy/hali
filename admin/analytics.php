<?php
require_once '../config/config.php';
requireLogin();
requireRole('admin');

$pdo = getDBConnection();

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$totalUsers = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM courses");
$totalCourses = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM enrollments");
$totalEnrollments = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT SUM(amount) as total FROM transactions WHERE status = 'completed'");
$totalRevenue = $stmt->fetch()['total'] ?? 0;

// Get monthly revenue
$stmt = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as revenue
    FROM transactions
    WHERE status = 'completed'
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12
");
$monthlyRevenue = $stmt->fetchAll();

// Get user growth
$stmt = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
    FROM users
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12
");
$userGrowth = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="/" class="navbar-brand">
                <i data-lucide="bar-chart-3"></i>
                <span><?php echo SITE_NAME; ?> Analytics</span>
            </a>
        </div>
    </nav>

    <div class="dashboard-container">
        <aside class="sidebar">
            <nav>
                <ul class="sidebar-nav">
                    <li><a href="/admin/dashboard-enhanced.php"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
                    <li><a href="/admin/analytics.php" class="active"><i data-lucide="bar-chart-3"></i> Analytics</a></li>
                    <li><a href="/admin/users.php"><i data-lucide="users"></i> Users</a></li>
                    <li><a href="/admin/courses.php"><i data-lucide="book-open"></i> Courses</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>Analytics</h1>
                <p>Platform performance and growth metrics</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon"><i data-lucide="users"></i></div>
                    <div class="stat-content">
                        <h3>Total Users</h3>
                        <p class="stat-value"><?php echo $totalUsers; ?></p>
                    </div>
                </div>
                <div class="stat-card secondary">
                    <div class="stat-icon"><i data-lucide="book-open"></i></div>
                    <div class="stat-content">
                        <h3>Total Courses</h3>
                        <p class="stat-value"><?php echo $totalCourses; ?></p>
                    </div>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon"><i data-lucide="graduation-cap"></i></div>
                    <div class="stat-content">
                        <h3>Total Enrollments</h3>
                        <p class="stat-value"><?php echo $totalEnrollments; ?></p>
                    </div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-icon"><i data-lucide="trending-up"></i></div>
                    <div class="stat-content">
                        <h3>Total Revenue</h3>
                        <p class="stat-value"><?php echo formatCurrency($totalRevenue); ?></p>
                    </div>
                </div>
            </div>

            <div class="card" style="padding: 2rem; margin-top: 2rem;">
                <h2>Monthly Revenue</h2>
                <table style="width: 100%; margin-top: 1rem;">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($monthlyRevenue as $data): ?>
                            <tr>
                                <td><?php echo $data['month']; ?></td>
                                <td><?php echo formatCurrency($data['revenue']); ?></td>
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
