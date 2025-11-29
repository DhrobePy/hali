<?php
require_once '../config/config.php';
requireLogin();
requireRole('admin');

$pdo = getDBConnection();

$stmt = $pdo->query("
    SELECT s.*, COUNT(us.id) as active_users
    FROM subscriptions s
    LEFT JOIN user_subscriptions us ON s.id = us.subscription_id AND us.status = 'active'
    GROUP BY s.id
");
$subscriptions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscriptions - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="/" class="navbar-brand">
                <i data-lucide="credit-card"></i>
                <span><?php echo SITE_NAME; ?> Subscriptions</span>
            </a>
        </div>
    </nav>

    <div class="dashboard-container">
        <aside class="sidebar">
            <nav>
                <ul class="sidebar-nav">
                    <li><a href="/admin/dashboard-enhanced.php"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
                    <li><a href="/admin/subscriptions.php" class="active"><i data-lucide="credit-card"></i> Subscriptions</a></li>
                    <li><a href="/admin/payments.php"><i data-lucide="wallet"></i> Payments</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>Subscription Plans</h1>
                <p>Manage subscription tiers and pricing</p>
            </div>

            <div class="card" style="padding: 2rem;">
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Plan Name</th>
                                <th>Price</th>
                                <th>Duration</th>
                                <th>Active Users</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subscriptions as $sub): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($sub['name']); ?></strong></td>
                                    <td><?php echo formatCurrency($sub['price']); ?></td>
                                    <td><?php echo $sub['duration_days']; ?> days</td>
                                    <td><?php echo $sub['active_users']; ?></td>
                                    <td>
                                        <span style="background: rgba(16, 185, 129, 0.2); color: var(--success); padding: 0.25rem 0.75rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.85rem;">Active</span>
                                    </td>
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
