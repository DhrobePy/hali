<?php
require_once '../config/config.php';

requireLogin();
requireRole('admin');

$user = getCurrentUser();
$pdo = getDBConnection();

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'student'");
$totalStudents = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'instructor'");
$totalInstructors = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM courses");
$totalCourses = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM enrollments");
$totalEnrollments = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'");
$totalRevenue = $stmt->fetch()['total'] ?? 0;

// Recent enrollments
$stmt = $pdo->query("
    SELECT e.*, u.name as student_name, c.title as course_title
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    ORDER BY e.enrolled_at DESC
    LIMIT 10
");
$recentEnrollments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 280px;
            background: var(--muted);
            padding: var(--spacing-xl);
            border-right: 1px solid var(--border);
        }
        .main-content {
            flex: 1;
            padding: var(--spacing-2xl);
            background: #f8f9fa;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-xl);
            margin-bottom: var(--spacing-2xl);
        }
        .stat-card {
            background: white;
            padding: var(--spacing-xl);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
        }
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: var(--spacing-sm);
        }
        .stat-label {
            color: var(--muted-foreground);
            font-size: 0.875rem;
        }
        .data-table {
            width: 100%;
            background: white;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }
        .data-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th {
            background: var(--muted);
            padding: var(--spacing-md);
            text-align: left;
            font-weight: 600;
            font-size: 0.875rem;
        }
        .data-table td {
            padding: var(--spacing-md);
            border-top: 1px solid var(--border);
        }
        .sidebar-nav {
            list-style: none;
        }
        .sidebar-nav li {
            margin-bottom: var(--spacing-sm);
        }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            padding: var(--spacing-md);
            border-radius: var(--radius-md);
            color: var(--foreground);
            transition: var(--transition);
        }
        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: white;
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div style="margin-bottom: var(--spacing-2xl);">
                <a href="/" style="display: flex; align-items: center; gap: var(--spacing-sm); margin-bottom: var(--spacing-lg);">
                    <i data-lucide="book-open" style="width: 24px; height: 24px; color: var(--primary);"></i>
                    <span style="font-weight: 700;" class="text-gradient"><?php echo SITE_NAME; ?></span>
                </a>
                <div>
                    <div style="font-weight: 600; margin-bottom: var(--spacing-xs);"><?php echo htmlspecialchars($user['name']); ?></div>
                    <div style="font-size: 0.875rem; color: var(--muted-foreground);">Administrator</div>
                </div>
            </div>
            
            <nav>
                <ul class="sidebar-nav">
                    <li><a href="/admin/dashboard.php" class="active"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
                    <li><a href="/admin/users.php"><i data-lucide="users"></i> Users</a></li>
                    <li><a href="/admin/courses.php"><i data-lucide="book-open"></i> Courses</a></li>
                    <li><a href="/admin/categories.php"><i data-lucide="folder"></i> Categories</a></li>
                    <li><a href="/admin/subscriptions.php"><i data-lucide="credit-card"></i> Subscriptions</a></li>
                    <li><a href="/admin/payments.php"><i data-lucide="dollar-sign"></i> Payments</a></li>
                    <li><a href="/admin/reviews.php"><i data-lucide="star"></i> Reviews</a></li>
                    <li><a href="/admin/settings.php"><i data-lucide="settings"></i> Settings</a></li>
                    <li><a href="/logout.php"><i data-lucide="log-out"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div style="margin-bottom: var(--spacing-2xl);">
                <h1>Admin Dashboard</h1>
                <p class="text-muted">Overview of your LMS platform</p>
            </div>
            
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($totalStudents); ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($totalInstructors); ?></div>
                    <div class="stat-label">Total Instructors</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($totalCourses); ?></div>
                    <div class="stat-label">Total Courses</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($totalEnrollments); ?></div>
                    <div class="stat-label">Total Enrollments</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?php echo formatCurrency($totalRevenue); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>
            
            <!-- Recent Enrollments -->
            <div>
                <h2 style="margin-bottom: var(--spacing-lg);">Recent Enrollments</h2>
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Status</th>
                                <th>Progress</th>
                                <th>Enrolled Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recentEnrollments) > 0): ?>
                                <?php foreach ($recentEnrollments as $enrollment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($enrollment['student_name']); ?></td>
                                        <td><?php echo htmlspecialchars($enrollment['course_title']); ?></td>
                                        <td>
                                            <span class="course-badge" style="background: <?php echo $enrollment['status'] === 'completed' ? 'var(--success)' : 'var(--primary)'; ?>; color: white;">
                                                <?php echo ucfirst($enrollment['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $enrollment['progress']; ?>%</td>
                                        <td><?php echo date('M d, Y', strtotime($enrollment['enrolled_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: var(--spacing-2xl); color: var(--muted-foreground);">
                                        No enrollments yet
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
