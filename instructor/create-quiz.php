<?php
require_once '../config/config.php';
requireLogin();
requireRole('instructor');

$user = getCurrentUser();
$courseId = intval($_GET['course_id'] ?? 0);
$pdo = getDBConnection();

// Verify course ownership
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND instructor_id = ?");
$stmt->execute([$courseId, $user['id']]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: /instructor/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $passingScore = intval($_POST['passing_score']);
    $timeLimit = intval($_POST['time_limit']);
    
    $stmt = $pdo->prepare("
        INSERT INTO quizzes (course_id, title, description, passing_score, time_limit_minutes, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$courseId, $title, $description, $passingScore, $timeLimit]);
    
    header('Location: /instructor/quizzes.php?course_id=' . $courseId);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Quiz - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <h1>Create Quiz for: <?php echo htmlspecialchars($course['title']); ?></h1>
            
            <div class="card" style="max-width: 600px; padding: var(--spacing-2xl);">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Quiz Title</label>
                        <input type="text" name="title" required class="form-input" placeholder="Module 1 Quiz">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-input" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Passing Score (%)</label>
                        <input type="number" name="passing_score" required class="form-input" placeholder="70" value="70" min="0" max="100">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Time Limit (Minutes)</label>
                        <input type="number" name="time_limit" required class="form-input" placeholder="30" value="30" min="5">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Create Quiz</button>
                </form>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
