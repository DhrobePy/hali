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

// Recent transactions
$stmt = $pdo->query("
    SELECT t.*, u.name FROM transactions t
    JOIN users u ON t.user_id = u.id
    ORDER BY t.created_at DESC
    LIMIT 5
");
$recentTransactions = $stmt->fetchAll();

// Recent users
$stmt = $pdo->query("
    SELECT id, name, email, role, created_at FROM users
    ORDER BY created_at DESC
    LIMIT 5
");
$recentUsers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style-enhanced.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="/" class="navbar-brand">
                <i data-lucide="shield-check"></i>
                <span><?php echo SITE_NAME; ?> Admin</span>
            </a>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <span style="color: var(--text-secondary);">Admin User</span>
                <a href="/logout.php" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                    <i data-lucide="log-out" style="width: 16px; height: 16px;"></i>
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <aside class="sidebar">
            <nav>
                <ul class="sidebar-nav">
                    <li><a href="/admin/dashboard-enhanced.php" class="active"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
                    <li><a href="/admin/users.php"><i data-lucide="users"></i> Users</a></li>
                    <li><a href="/admin/courses.php"><i data-lucide="book-open"></i> Courses</a></li>
                    <li><a href="/admin/categories.php"><i data-lucide="folder"></i> Categories</a></li>
                    <li><a href="/admin/subscriptions.php"><i data-lucide="credit-card"></i> Subscriptions</a></li>
                    <li><a href="/admin/payments.php"><i data-lucide="wallet"></i> Payments</a></li>
                    <li><a href="/admin/reviews.php"><i data-lucide="star"></i> Reviews</a></li>
                    <li><a href="/admin/analytics.php"><i data-lucide="bar-chart-3"></i> Analytics</a></li>
                    <li><a href="/admin/reports.php"><i data-lucide="file-text"></i> Reports</a></li>
                    <li><a href="/admin/settings.php"><i data-lucide="settings"></i> Settings</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Admin Dashboard</h1>
                <p>Welcome back! Here's your platform overview.</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon">
                        <i data-lucide="users"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total Users</h3>
                        <p class="stat-value"><?php echo $totalUsers; ?></p>
                    </div>
                </div>

                <div class="stat-card secondary">
                    <div class="stat-icon">
                        <i data-lucide="book-open"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total Courses</h3>
                        <p class="stat-value"><?php echo $totalCourses; ?></p>
                    </div>
                </div>

                <div class="stat-card success">
                    <div class="stat-icon">
                        <i data-lucide="graduation-cap"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total Enrollments</h3>
                        <p class="stat-value"><?php echo $totalEnrollments; ?></p>
                    </div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-icon">
                        <i data-lucide="trending-up"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total Revenue</h3>
                        <p class="stat-value"><?php echo formatCurrency($totalRevenue); ?></p>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="card" style="padding: 2rem; margin-bottom: 2rem;">
                <h2 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="credit-card" style="color: var(--primary);"></i>
                    Recent Transactions
                </h2>
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Amount</th>
                                <th>Gateway</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentTransactions as $txn): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($txn['name']); ?></td>
                                    <td><strong><?php echo formatCurrency($txn['amount']); ?></strong></td>
                                    <td><span style="text-transform: uppercase; font-weight: 600; color: var(--primary);"><?php echo htmlspecialchars($txn['gateway']); ?></span></td>
                                    <td>
                                        <?php if ($txn['status'] === 'completed'): ?>
                                            <span style="background: rgba(16, 185, 129, 0.2); color: var(--success); padding: 0.25rem 0.75rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.85rem;">Completed</span>
                                        <?php elseif ($txn['status'] === 'pending'): ?>
                                            <span style="background: rgba(245, 158, 11, 0.2); color: var(--warning); padding: 0.25rem 0.75rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.85rem;">Pending</span>
                                        <?php else: ?>
                                            <span style="background: rgba(239, 68, 68, 0.2); color: var(--danger); padding: 0.25rem 0.75rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.85rem;">Failed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($txn['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="card" style="padding: 2rem;">
                <h2 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="user-plus" style="color: var(--secondary);"></i>
                    Recent Users
                </h2>
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentUsers as $user): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($user['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span style="background: rgba(99, 102, 241, 0.2); color: var(--primary); padding: 0.25rem 0.75rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.85rem;">Admin</span>
                                        <?php elseif ($user['role'] === 'instructor'): ?>
                                            <span style="background: rgba(6, 182, 212, 0.2); color: var(--tertiary); padding: 0.25rem 0.75rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.85rem;">Instructor</span>
                                        <?php else: ?>
                                            <span style="background: rgba(16, 185, 129, 0.2); color: var(--success); padding: 0.25rem 0.75rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.85rem;">Student</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
