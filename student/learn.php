<?php
require_once '../config/config.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDBConnection();
$courseId = intval($_GET['course_id'] ?? 0);

// Get course
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    die('Course not found');
}

// Check enrollment
if (!isEnrolled($courseId, $user['id'])) {
    die('You are not enrolled in this course');
}

// Get modules and lessons
$stmt = $pdo->prepare("
    SELECT m.id, m.title,
    (SELECT JSON_ARRAYAGG(JSON_OBJECT('id', l.id, 'title', l.title, 'video_url', l.video_url)) FROM lessons l WHERE l.module_id = m.id) as lessons
    FROM modules m
    WHERE m.course_id = ?
    ORDER BY m.created_at
");
$stmt->execute([$courseId]);
$modules = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="/student/courses.php" class="navbar-brand">
                <i data-lucide="arrow-left"></i>
                <span><?php echo htmlspecialchars($course['title']); ?></span>
            </a>
        </div>
    </nav>

    <div style="display: grid; grid-template-columns: 300px 1fr; min-height: 100vh;">
        <aside style="background: var(--bg-primary); border-right: 1px solid var(--border-color); padding: 2rem; overflow-y: auto;">
            <h3 style="margin-bottom: 1rem;">Course Content</h3>
            <?php foreach ($modules as $module): ?>
                <div style="margin-bottom: 1.5rem;">
                    <h4 style="font-weight: 600; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($module['title']); ?></h4>
                    <?php if ($module['lessons']): ?>
                        <?php $lessons = json_decode($module['lessons'], true); ?>
                        <?php foreach ($lessons as $lesson): ?>
                            <a href="?course_id=<?php echo $courseId; ?>&lesson_id=<?php echo $lesson['id']; ?>" style="display: block; padding: 0.5rem; color: var(--text-secondary); text-decoration: none; border-radius: 0.5rem; margin-bottom: 0.25rem; transition: all 0.3s;">
                                <i data-lucide="play-circle" style="width: 16px; height: 16px; display: inline; margin-right: 0.5rem;"></i>
                                <?php echo htmlspecialchars($lesson['title']); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </aside>

        <main style="padding: 2rem;">
            <div class="card" style="padding: 2rem;">
                <h1><?php echo htmlspecialchars($course['title']); ?></h1>
                <p style="color: var(--text-secondary); margin-bottom: 2rem;"><?php echo htmlspecialchars($course['description']); ?></p>
                
                <?php if (isset($_GET['lesson_id'])): ?>
                    <?php
                    $lessonId = intval($_GET['lesson_id']);
                    $stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ? AND module_id IN (SELECT id FROM modules WHERE course_id = ?)");
                    $stmt->execute([$lessonId, $courseId]);
                    $lesson = $stmt->fetch();
                    
                    if ($lesson):
                    ?>
                        <h2><?php echo htmlspecialchars($lesson['title']); ?></h2>
                        <?php if ($lesson['video_url']): ?>
                            <div style="background: #000; aspect-ratio: 16/9; border-radius: 1rem; margin: 2rem 0; display: flex; align-items: center; justify-content: center;">
                                <iframe width="100%" height="100%" style="border-radius: 1rem;" src="<?php echo htmlspecialchars($lesson['video_url']); ?>" frameborder="0" allowfullscreen></iframe>
                            </div>
                        <?php endif; ?>
                        <p><?php echo htmlspecialchars($lesson['content']); ?></p>
                        
                        <a href="/api/courses/mark-complete.php?lesson_id=<?php echo $lessonId; ?>" class="btn btn-success" style="margin-top: 2rem;">
                            <i data-lucide="check-circle"></i>
                            Mark as Complete
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <p style="text-align: center; color: var(--text-secondary);">Select a lesson from the left to start learning</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
