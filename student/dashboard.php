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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - <?php echo SITE_NAME; ?></title>
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
        .sidebar-header {
            margin-bottom: var(--spacing-2xl);
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
        .main-content {
            flex: 1;
            padding: var(--spacing-2xl);
        }
        .dashboard-header {
            margin-bottom: var(--spacing-2xl);
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
        .stat-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--spacing-md);
        }
        .stat-card-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(102, 126, 234, 0.1);
            border-radius: var(--radius-md);
            color: var(--primary);
        }
        .stat-card-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
        }
        .stat-card-label {
            color: var(--muted-foreground);
            font-size: 0.875rem;
        }
        .progress-bar {
            width: 100%;
            height: 8px;
            background: var(--muted);
            border-radius: 999px;
            overflow: hidden;
            margin-top: var(--spacing-sm);
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            transition: width 0.3s ease;
        }
        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid var(--border);
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="/" style="display: flex; align-items: center; gap: var(--spacing-sm); margin-bottom: var(--spacing-lg);">
                    <i data-lucide="book-open" style="width: 24px; height: 24px; color: var(--primary);"></i>
                    <span style="font-weight: 700;" class="text-gradient"><?php echo SITE_NAME; ?></span>
                </a>
                <div>
                    <div style="font-weight: 600; margin-bottom: var(--spacing-xs);"><?php echo htmlspecialchars($user['name']); ?></div>
                    <div style="font-size: 0.875rem; color: var(--muted-foreground);"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
            </div>
            
            <nav>
                <ul class="sidebar-nav">
                    <li><a href="/student/dashboard.php" class="active"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
                    <li><a href="/student/courses.php"><i data-lucide="book-open"></i> My Courses</a></li>
                    <li><a href="/student/progress.php"><i data-lucide="bar-chart"></i> Progress</a></li>
                    <li><a href="/student/certificates.php"><i data-lucide="award"></i> Certificates</a></li>
                    <li><a href="/student/subscription.php"><i data-lucide="credit-card"></i> Subscription</a></li>
                    <li><a href="/student/profile.php"><i data-lucide="user"></i> Profile</a></li>
                    <li><a href="/logout.php"><i data-lucide="log-out"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1>Welcome back, <?php echo htmlspecialchars($user['name']); ?>! 👋</h1>
                <p class="text-muted">Continue your learning journey</p>
            </div>
            
            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-card-value"><?php echo count($enrollments); ?></div>
                            <div class="stat-card-label">Enrolled Courses</div>
                        </div>
                        <div class="stat-card-icon">
                            <i data-lucide="book-open"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-card-value">
                                <?php 
                                $completed = array_filter($enrollments, fn($e) => $e['status'] === 'completed');
                                echo count($completed);
                                ?>
                            </div>
                            <div class="stat-card-label">Completed</div>
                        </div>
                        <div class="stat-card-icon">
                            <i data-lucide="check-circle"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-card-value">
                                <?php 
                                $avgProgress = count($enrollments) > 0 
                                    ? round(array_sum(array_column($enrollments, 'progress')) / count($enrollments))
                                    : 0;
                                echo $avgProgress . '%';
                                ?>
                            </div>
                            <div class="stat-card-label">Avg. Progress</div>
                        </div>
                        <div class="stat-card-icon">
                            <i data-lucide="bar-chart"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- My Courses -->
            <div>
                <h2 style="margin-bottom: var(--spacing-xl);">Continue Learning</h2>
                
                <?php if (count($enrollments) > 0): ?>
                    <div class="course-grid">
                        <?php foreach ($enrollments as $enrollment): ?>
                            <div class="course-card">
                                <?php if ($enrollment['thumbnail_url']): ?>
                                    <img src="<?php echo htmlspecialchars($enrollment['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($enrollment['title']); ?>" class="course-thumbnail">
                                <?php else: ?>
                                    <div class="course-thumbnail" style="display: flex; align-items: center; justify-content: center;">
                                        <i data-lucide="book-open" style="width: 64px; height: 64px; color: rgba(255,255,255,0.5);"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="course-content">
                                    <div class="course-meta">
                                        <span class="course-badge"><?php echo ucfirst($enrollment['level']); ?></span>
                                        <span class="course-badge" style="background: <?php echo $enrollment['status'] === 'completed' ? 'var(--success)' : 'var(--primary)'; ?>; color: white;">
                                            <?php echo ucfirst($enrollment['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <h3 class="course-title"><?php echo htmlspecialchars($enrollment['title']); ?></h3>
                                    <p class="text-muted" style="font-size: 0.875rem; margin-bottom: var(--spacing-md);">
                                        by <?php echo htmlspecialchars($enrollment['instructor_name']); ?>
                                    </p>
                                    
                                    <div style="margin-bottom: var(--spacing-md);">
                                        <div style="display: flex; justify-content: space-between; margin-bottom: var(--spacing-xs);">
                                            <span style="font-size: 0.875rem; font-weight: 600;">Progress</span>
                                            <span style="font-size: 0.875rem; color: var(--primary); font-weight: 600;"><?php echo $enrollment['progress']; ?>%</span>
                                        </div>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo $enrollment['progress']; ?>%;"></div>
                                        </div>
                                    </div>
                                    
                                    <a href="/course.php?slug=<?php echo urlencode($enrollment['slug']); ?>" class="btn btn-primary" style="width: 100%;">
                                        <?php echo $enrollment['progress'] > 0 ? 'Continue Learning' : 'Start Course'; ?>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="card" style="text-align: center; padding: var(--spacing-3xl);">
                        <i data-lucide="book-open" style="width: 64px; height: 64px; margin: 0 auto var(--spacing-lg); color: var(--muted-foreground);"></i>
                        <h3>No Courses Yet</h3>
                        <p class="text-muted">Start learning by enrolling in a course</p>
                        <a href="/" class="btn btn-primary" style="margin-top: var(--spacing-lg);">Browse Courses</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
