<?php
require_once '../config/config.php';
require_once '../includes/Course.php';
requireLogin();

$user = getCurrentUser();
$courseId = intval($_GET['course'] ?? 0);
$lessonId = intval($_GET['lesson'] ?? 0);

// Check enrollment
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
$stmt->execute([$user['id'], $courseId]);
$enrollment = $stmt->fetch();

if (!$enrollment) {
    header('Location: /student/dashboard.php');
    exit;
}

$course = Course::getById($courseId);
$modules = Course::getModulesWithLessons($courseId);

// Get current lesson
$currentLesson = null;
if ($lessonId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ?");
    $stmt->execute([$lessonId]);
    $currentLesson = $stmt->fetch();
} else {
    // Get first lesson
    if (count($modules) > 0 && count($modules[0]['lessons']) > 0) {
        $currentLesson = $modules[0]['lessons'][0];
        $lessonId = $currentLesson['id'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($course['title']); ?> - Learning</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .learn-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            height: 100vh;
        }
        .video-section {
            background: #000;
            display: flex;
            flex-direction: column;
        }
        .video-player {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000;
        }
        .video-info {
            background: white;
            padding: var(--spacing-xl);
        }
        .curriculum-sidebar {
            background: var(--muted);
            overflow-y: auto;
            padding: var(--spacing-lg);
        }
        @media (max-width: 1024px) {
            .learn-container {
                grid-template-columns: 1fr;
            }
            .curriculum-sidebar {
                max-height: 400px;
            }
        }
    </style>
</head>
<body>
    <div class="learn-container">
        <div class="video-section">
            <div class="video-player">
                <?php if ($currentLesson && $currentLesson['video_url']): ?>
                    <iframe 
                        src="<?php echo htmlspecialchars($currentLesson['video_url']); ?>" 
                        style="width: 100%; height: 100%; border: none;"
                        allow="autoplay; fullscreen; picture-in-picture"
                        allowfullscreen>
                    </iframe>
                <?php else: ?>
                    <div style="color: white; text-align: center;">
                        <i data-lucide="video-off" style="width: 64px; height: 64px; margin-bottom: var(--spacing-lg);"></i>
                        <p>No video available for this lesson</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="video-info">
                <h2><?php echo htmlspecialchars($currentLesson['title'] ?? 'Select a lesson'); ?></h2>
                <?php if ($currentLesson && $currentLesson['description']): ?>
                    <p class="text-muted"><?php echo htmlspecialchars($currentLesson['description']); ?></p>
                <?php endif; ?>
                
                <div style="margin-top: var(--spacing-lg);">
                    <button class="btn btn-primary" onclick="markComplete()">Mark as Complete</button>
                    <a href="/student/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>
            </div>
        </div>
        
        <div class="curriculum-sidebar">
            <h3 style="margin-bottom: var(--spacing-lg);">Course Content</h3>
            
            <?php foreach ($modules as $module): ?>
                <div class="module-item" style="margin-bottom: var(--spacing-md);">
                    <div class="module-header"><?php echo htmlspecialchars($module['title']); ?></div>
                    <div class="lesson-list">
                        <?php foreach ($module['lessons'] as $lesson): ?>
                            <a href="?course=<?php echo $courseId; ?>&lesson=<?php echo $lesson['id']; ?>" 
                               class="lesson-item <?php echo $lesson['id'] == $lessonId ? 'active' : ''; ?>"
                               style="text-decoration: none; <?php echo $lesson['id'] == $lessonId ? 'background: white;' : ''; ?>">
                                <i data-lucide="play-circle" style="width: 20px; height: 20px;"></i>
                                <span><?php echo htmlspecialchars($lesson['title']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <script>
        lucide.createIcons();
        
        function markComplete() {
            fetch('/api/courses/mark-complete.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    lesson_id: <?php echo $lessonId; ?>,
                    course_id: <?php echo $courseId; ?>
                })
            }).then(() => alert('Lesson marked as complete!'));
        }
    </script>
</body>
</html>
EOF1
<?php require_once 'config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>404 - Page Not Found</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: var(--spacing-xl);">
        <div>
            <i data-lucide="alert-circle" style="width: 64px; height: 64px; color: var(--primary); margin-bottom: var(--spacing-lg);"></i>
            <h1 style="font-size: 4rem; margin-bottom: var(--spacing-md);">404</h1>
            <h2 style="margin-bottom: var(--spacing-lg);">Page Not Found</h2>
            <p class="text-muted" style="margin-bottom: var(--spacing-xl);">The page you're looking for doesn't exist.</p>
            <a href="/" class="btn btn-primary">Go Home</a>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
