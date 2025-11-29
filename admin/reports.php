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

$stmt = $pdo->query("SELECT COUNT(*) as count FROM transactions WHERE status = 'completed'");
$totalTransactions = $stmt->fetch()['count'];

// Revenue by month
$stmt = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as revenue
    FROM transactions
    WHERE status = 'completed'
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12
");
$revenueByMonth = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analytics - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <h1>Platform Analytics</h1>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i data-lucide="users"></i></div>
                    <div class="stat-content">
                        <p class="stat-label">Total Users</p>
                        <p class="stat-value"><?php echo $totalUsers; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"><i data-lucide="book-open"></i></div>
                    <div class="stat-content">
                        <p class="stat-label">Total Courses</p>
                        <p class="stat-value"><?php echo $totalCourses; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"><i data-lucide="graduation-cap"></i></div>
                    <div class="stat-content">
                        <p class="stat-label">Total Enrollments</p>
                        <p class="stat-value"><?php echo $totalEnrollments; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"><i data-lucide="credit-card"></i></div>
                    <div class="stat-content">
                        <p class="stat-label">Total Revenue</p>
                        <p class="stat-value"><?php echo formatCurrency($totalRevenue); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="card" style="padding: var(--spacing-2xl); margin-top: var(--spacing-2xl);">
                <h3>Revenue Trend (Last 12 Months)</h3>
                <canvas id="revenueChart"></canvas>
            </div>
        </main>
    </div>
    
    <script>
        lucide.createIcons();
        
        const labels = <?php echo json_encode(array_column($revenueByMonth, 'month')); ?>;
        const data = <?php echo json_encode(array_column($revenueByMonth, 'revenue')); ?>;
        
        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue (BDT)',
                    data: data,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>
EOF1
<?php
require_once '../config/config.php';
requireLogin();
requireRole('admin');

$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportType = sanitize($_POST['report_type']);
    $startDate = sanitize($_POST['start_date']);
    $endDate = sanitize($_POST['end_date']);
    
    if ($reportType === 'revenue') {
        $stmt = $pdo->prepare("
            SELECT DATE(created_at) as date, SUM(amount) as total, COUNT(*) as count
            FROM transactions
            WHERE status = 'completed' AND created_at BETWEEN ? AND ?
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ");
        $stmt->execute([$startDate, $endDate]);
        $data = $stmt->fetchAll();
    } elseif ($reportType === 'enrollments') {
        $stmt = $pdo->prepare("
            SELECT c.title, COUNT(e.id) as count
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            WHERE e.enrolled_at BETWEEN ? AND ?
            GROUP BY c.id
            ORDER BY count DESC
        ");
        $stmt->execute([$startDate, $endDate]);
        $data = $stmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <h1>Generate Reports</h1>
            
            <div class="card" style="padding: var(--spacing-2xl); max-width: 600px;">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Report Type</label>
                        <select name="report_type" required class="form-input">
                            <option value="revenue">Revenue Report</option>
                            <option value="enrollments">Enrollment Report</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" required class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" required class="form-input">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </form>
            </div>
            
            <?php if (isset($data) && count($data) > 0): ?>
                <div class="data-table" style="margin-top: var(--spacing-2xl);">
                    <table>
                        <thead>
                            <tr>
                                <?php foreach (array_keys($data[0]) as $column): ?>
                                    <th><?php echo ucfirst(str_replace('_', ' ', $column)); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $row): ?>
                                <tr>
                                    <?php foreach ($row as $value): ?>
                                        <td><?php echo htmlspecialchars($value); ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
