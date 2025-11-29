<?php
require_once 'config/config.php';
require_once 'includes/Course.php';

$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: /');
    exit;
}

$course = Course::getBySlug($slug);

if (!$course) {
    header('Location: /404.php');
    exit;
}

$modules = Course::getModulesWithLessons($course['id']);
$reviews = Course::getReviews($course['id'], 10);

$isEnrolled = false;
$hasAccess = false;

if (isLoggedIn()) {
    $user = getCurrentUser();
    $pdo = getDBConnection();
    
    // Check enrollment
    $stmt = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$user['id'], $course['id']]);
    $enrollment = $stmt->fetch();
    $isEnrolled = $enrollment !== false;
    
    // Check access
    $hasAccess = Course::hasAccess($user['id'], $course['id']);
}

// Calculate total lessons and duration
$totalLessons = 0;
$totalDuration = 0;
foreach ($modules as $module) {
    $totalLessons += count($module['lessons']);
    foreach ($module['lessons'] as $lesson) {
        $totalDuration += $lesson['video_duration'] ?? 0;
    }
}

$hours = floor($totalDuration / 3600);
$minutes = floor(($totalDuration % 3600) / 60);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($course['description'] ?? ''); ?>">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .course-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: var(--spacing-3xl) 0;
        }
        .course-content {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: var(--spacing-2xl);
            margin-top: calc(var(--spacing-2xl) * -1);
        }
        .course-main {
            background: white;
            border-radius: var(--radius-xl);
            padding: var(--spacing-2xl);
            box-shadow: var(--shadow-xl);
        }
        .course-sidebar {
            position: sticky;
            top: var(--spacing-xl);
            height: fit-content;
        }
        .course-card {
            background: white;
            border-radius: var(--radius-xl);
            padding: var(--spacing-xl);
            box-shadow: var(--shadow-xl);
        }
        .module-item {
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            margin-bottom: var(--spacing-md);
            overflow: hidden;
        }
        .module-header {
            padding: var(--spacing-lg);
            background: var(--muted);
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .lesson-list {
            padding: var(--spacing-md);
        }
        .lesson-item {
            padding: var(--spacing-md);
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            border-radius: var(--radius-md);
            transition: var(--transition);
        }
        .lesson-item:hover {
            background: var(--muted);
        }
        .lesson-item.locked {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .review-item {
            padding: var(--spacing-lg);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            margin-bottom: var(--spacing-md);
        }
        .rating-stars {
            display: flex;
            gap: 2px;
            color: #fbbf24;
        }
        @media (max-width: 1024px) {
            .course-content {
                grid-template-columns: 1fr;
            }
            .course-sidebar {
                position: static;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container navbar-container">
            <a href="/" class="navbar-brand">
                <i data-lucide="book-open" style="width: 32px; height: 32px;"></i>
                <span class="text-gradient"><?php echo SITE_NAME; ?></span>
            </a>
            
            <div style="display: flex; align-items: center; gap: 1rem;">
                <?php if (isLoggedIn()): ?>
                    <a href="/student/dashboard.php" class="btn btn-secondary">Dashboard</a>
                <?php else: ?>
                    <a href="/login.php" class="btn btn-secondary">Sign In</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <!-- Course Header -->
    <section class="course-header">
        <div class="container">
            <div style="max-width: 800px;">
                <div style="margin-bottom: var(--spacing-md);">
                    <span class="hero-badge"><?php echo ucfirst($course['level']); ?></span>
                </div>
                
                <h1 style="color: white; margin-bottom: var(--spacing-lg);">
                    <?php echo htmlspecialchars($course['title']); ?>
                </h1>
                
                <p style="font-size: 1.25rem; color: rgba(255,255,255,0.9); margin-bottom: var(--spacing-xl);">
                    <?php echo htmlspecialchars($course['description'] ?? ''); ?>
                </p>
                
                <div style="display: flex; align-items: center; gap: var(--spacing-xl); flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
                        <i data-lucide="user" style="width: 20px; height: 20px;"></i>
                        <span><?php echo htmlspecialchars($course['instructor_name']); ?></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
                        <i data-lucide="star" style="width: 20px; height: 20px;"></i>
                        <span><?php echo number_format($course['rating'] / 100, 1); ?> (<?php echo number_format($course['review_count']); ?> reviews)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
                        <i data-lucide="users" style="width: 20px; height: 20px;"></i>
                        <span><?php echo number_format($course['enrollment_count']); ?> students</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
                        <i data-lucide="clock" style="width: 20px; height: 20px;"></i>
                        <span><?php echo $hours; ?>h <?php echo $minutes; ?>m</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Course Content -->
    <section class="section">
        <div class="container">
            <div class="course-content">
                <!-- Main Content -->
                <div class="course-main">
                    <!-- What You'll Learn -->
                    <div style="margin-bottom: var(--spacing-2xl);">
                        <h2>What You'll Learn</h2>
                        <p class="text-muted">Master the skills covered in this comprehensive course</p>
                    </div>
                    
                    <!-- Course Curriculum -->
                    <div style="margin-bottom: var(--spacing-2xl);">
                        <h2>Course Curriculum</h2>
                        <p class="text-muted"><?php echo count($modules); ?> modules • <?php echo $totalLessons; ?> lessons</p>
                        
                        <div style="margin-top: var(--spacing-xl);">
                            <?php foreach ($modules as $module): ?>
                                <div class="module-item">
                                    <div class="module-header">
                                        <span><?php echo htmlspecialchars($module['title']); ?></span>
                                        <span class="text-muted" style="font-size: 0.875rem;"><?php echo count($module['lessons']); ?> lessons</span>
                                    </div>
                                    <div class="lesson-list">
                                        <?php foreach ($module['lessons'] as $lesson): ?>
                                            <div class="lesson-item <?php echo (!$hasAccess && !$lesson['is_free']) ? 'locked' : ''; ?>">
                                                <i data-lucide="<?php echo $lesson['is_free'] ? 'play-circle' : ($hasAccess ? 'play-circle' : 'lock'); ?>" style="width: 20px; height: 20px; color: var(--primary);"></i>
                                                <div style="flex: 1;">
                                                    <div style="font-weight: 500;"><?php echo htmlspecialchars($lesson['title']); ?></div>
                                                    <?php if ($lesson['is_free']): ?>
                                                        <div style="font-size: 0.75rem; color: var(--success);">FREE PREVIEW</div>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if ($lesson['video_duration']): ?>
                                                    <span class="text-muted" style="font-size: 0.875rem;">
                                                        <?php echo gmdate("i:s", $lesson['video_duration']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Reviews -->
                    <?php if (count($reviews) > 0): ?>
                        <div>
                            <h2>Student Reviews</h2>
                            <p class="text-muted"><?php echo number_format($course['review_count']); ?> reviews</p>
                            
                            <div style="margin-top: var(--spacing-xl);">
                                <?php foreach ($reviews as $review): ?>
                                    <div class="review-item">
                                        <div style="display: flex; align-items: center; gap: var(--spacing-md); margin-bottom: var(--spacing-md);">
                                            <?php if ($review['avatar_url']): ?>
                                                <img src="<?php echo htmlspecialchars($review['avatar_url']); ?>" alt="" style="width: 48px; height: 48px; border-radius: 50%;">
                                            <?php else: ?>
                                                <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--muted); display: flex; align-items: center; justify-content: center;">
                                                    <i data-lucide="user"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div style="font-weight: 600;"><?php echo htmlspecialchars($review['user_name']); ?></div>
                                                <div class="rating-stars">
                                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                                        <i data-lucide="star" style="width: 16px; height: 16px; <?php echo $i < $review['rating'] ? 'fill: currentColor;' : ''; ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-muted"><?php echo htmlspecialchars($review['comment']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Sidebar -->
                <div class="course-sidebar">
                    <div class="course-card">
                        <?php if ($course['thumbnail_url']): ?>
                            <img src="<?php echo htmlspecialchars($course['thumbnail_url']); ?>" alt="" style="width: 100%; border-radius: var(--radius-lg); margin-bottom: var(--spacing-lg);">
                        <?php endif; ?>
                        
                        <?php if ($course['price']): ?>
                            <div style="font-size: 2rem; font-weight: 700; color: var(--primary); margin-bottom: var(--spacing-lg);">
                                <?php echo formatCurrency($course['price']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($isEnrolled): ?>
                            <a href="/student/learn.php?course=<?php echo $course['id']; ?>" class="btn btn-primary" style="width: 100%; margin-bottom: var(--spacing-md);">
                                Continue Learning
                            </a>
                        <?php elseif ($hasAccess): ?>
                            <form method="POST" action="/api/courses/enroll.php">
                                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: var(--spacing-md);">
                                    Enroll Now
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="/register.php" class="btn btn-primary" style="width: 100%; margin-bottom: var(--spacing-md);">
                                Get Started
                            </a>
                        <?php endif; ?>
                        
                        <div style="padding: var(--spacing-lg); background: var(--muted); border-radius: var(--radius-lg);">
                            <div style="margin-bottom: var(--spacing-md);">
                                <div style="font-weight: 600; margin-bottom: var(--spacing-xs);">This course includes:</div>
                            </div>
                            <ul style="list-style: none; padding: 0;">
                                <li style="display: flex; align-items: center; gap: var(--spacing-sm); margin-bottom: var(--spacing-sm);">
                                    <i data-lucide="video" style="width: 20px; height: 20px; color: var(--primary);"></i>
                                    <span><?php echo $hours; ?>h <?php echo $minutes; ?>m video content</span>
                                </li>
                                <li style="display: flex; align-items: center; gap: var(--spacing-sm); margin-bottom: var(--spacing-sm);">
                                    <i data-lucide="file-text" style="width: 20px; height: 20px; color: var(--primary);"></i>
                                    <span><?php echo $totalLessons; ?> lessons</span>
                                </li>
                                <li style="display: flex; align-items: center; gap: var(--spacing-sm); margin-bottom: var(--spacing-sm);">
                                    <i data-lucide="award" style="width: 20px; height: 20px; color: var(--primary);"></i>
                                    <span>Certificate of completion</span>
                                </li>
                                <li style="display: flex; align-items: center; gap: var(--spacing-sm); margin-bottom: var(--spacing-sm);">
                                    <i data-lucide="infinity" style="width: 20px; height: 20px; color: var(--primary);"></i>
                                    <span>Lifetime access</span>
                                </li>
                                <li style="display: flex; align-items: center; gap: var(--spacing-sm);">
                                    <i data-lucide="smartphone" style="width: 20px; height: 20px; color: var(--primary);"></i>
                                    <span>Access on mobile and desktop</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
