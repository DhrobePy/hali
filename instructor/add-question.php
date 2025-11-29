<?php
require_once '../config/config.php';
requireLogin();
requireRole('instructor');

$user = getCurrentUser();
$quizId = intval($_GET['quiz_id'] ?? 0);
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
    $questionText = sanitize($_POST['question_text']);
    $options = json_encode(array_filter([$_POST['option1'], $_POST['option2'], $_POST['option3'], $_POST['option4']]));
    $correctAnswer = sanitize($_POST['correct_answer']);
    $order = intval($_POST['order']);
    
    $stmt = $pdo->prepare("
        INSERT INTO questions (quiz_id, question_text, options, correct_answer, question_order, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$quizId, $questionText, $options, $correctAnswer, $order]);
    
    header('Location: /instructor/quizzes.php?course_id=' . $quiz['course_id']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Question - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <h1>Add Question to: <?php echo htmlspecialchars($quiz['title']); ?></h1>
            
            <div class="card" style="max-width: 600px; padding: var(--spacing-2xl);">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Question</label>
                        <textarea name="question_text" required class="form-input" rows="2"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Option 1</label>
                        <input type="text" name="option1" required class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Option 2</label>
                        <input type="text" name="option2" required class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Option 3</label>
                        <input type="text" name="option3" class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Option 4</label>
                        <input type="text" name="option4" class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Correct Answer</label>
                        <select name="correct_answer" required class="form-input">
                            <option value="">Select correct answer</option>
                            <option value="option1">Option 1</option>
                            <option value="option2">Option 2</option>
                            <option value="option3">Option 3</option>
                            <option value="option4">Option 4</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Question Order</label>
                        <input type="number" name="order" required class="form-input" placeholder="1" value="1">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Add Question</button>
                </form>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
