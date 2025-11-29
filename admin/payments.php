<?php
require_once '../config/config.php';
requireLogin();
requireRole('admin');

$pdo = getDBConnection();
$stmt = $pdo->query("
    SELECT p.*, u.name as user_name, u.email as user_email
    FROM payments p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC
");
$payments = $stmt->fetchAll();

$stmt = $pdo->query("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'");
$totalRevenue = $stmt->fetch()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <div style="margin-bottom: var(--spacing-2xl);">
                <h1>Manage Payments</h1>
                <p class="text-muted">Total Revenue: <?php echo formatCurrency($totalRevenue); ?></p>
            </div>
            
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($payment['transaction_id']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($payment['user_name']); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($payment['user_email']); ?></small>
                                </td>
                                <td><?php echo formatCurrency($payment['amount']); ?></td>
                                <td><?php echo strtoupper($payment['payment_method']); ?></td>
                                <td>
                                    <span class="course-badge" style="background: <?php echo $payment['status'] === 'completed' ? 'var(--success)' : ($payment['status'] === 'pending' ? 'var(--warning)' : 'var(--error)'); ?>; color: white;">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($payment['created_at'])); ?></td>
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
