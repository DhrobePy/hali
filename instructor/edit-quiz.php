<?php
require_once '../config/config.php';
requireLogin();
requireRole('instructor');

$user = getCurrentUser();
$quizId = intval($_GET['id'] ?? 0);
$pdo = getDBConnection();

// Verify quiz ownership
$stmt = $pdo->prepare("
    SELECT q.* FROM quizzes q
    JOIN courses c ON q.course_id = c.id
    WHERE q.id = ? AND c.instructor_id = ?
");
$stmt->execute([$quizId, $user['id']]);
$quiz = $stmt->fetch();

if (!$quiz) {
    header('Location: /instructor/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $passingScore = intval($_POST['passing_score']);
    $timeLimit = intval($_POST['time_limit']);
    
    $stmt = $pdo->prepare("
        UPDATE quizzes 
        SET title = ?, description = ?, passing_score = ?, time_limit_minutes = ?
        WHERE id = ?
    ");
    $stmt->execute([$title, $description, $passingScore, $timeLimit, $quizId]);
    
    header('Location: /instructor/quizzes.php?course_id=' . $quiz['course_id']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Quiz - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <h1>Edit Quiz</h1>
            
            <div class="card" style="max-width: 600px; padding: var(--spacing-2xl);">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Quiz Title</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($quiz['title']); ?>" required class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-input" rows="3"><?php echo htmlspecialchars($quiz['description']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Passing Score (%)</label>
                        <input type="number" name="passing_score" value="<?php echo $quiz['passing_score']; ?>" required class="form-input" min="0" max="100">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Time Limit (Minutes)</label>
                        <input type="number" name="time_limit" value="<?php echo $quiz['time_limit_minutes']; ?>" required class="form-input" min="5">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Quiz</button>
                </form>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
