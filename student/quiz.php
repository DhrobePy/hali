<?php
require_once '../config/config.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDBConnection();
$quizId = intval($_GET['quiz_id'] ?? 0);

// Get quiz
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quizId]);
$quiz = $stmt->fetch();

if (!$quiz) {
    die('Quiz not found');
}

// Get quiz questions
$stmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY id");
$stmt->execute([$quizId]);
$questions = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = 0;
    $totalQuestions = count($questions);
    
    foreach ($questions as $question) {
        $userAnswer = $_POST['question_' . $question['id']] ?? '';
        if ($userAnswer === $question['correct_answer']) {
            $score++;
        }
    }
    
    $passed = ($score / $totalQuestions * 100) >= $quiz['passing_score'];
    
    // Save quiz attempt
    $stmt = $pdo->prepare("
        INSERT INTO quiz_attempts (quiz_id, user_id, score, total_questions, passed, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$quizId, $user['id'], $score, $totalQuestions, $passed ? 1 : 0]);
    
    redirect("/student/quiz.php?quiz_id=$quizId&result=1&score=$score&total=$totalQuestions&passed=" . ($passed ? 1 : 0));
}

// Check if already attempted
$stmt = $pdo->prepare("SELECT * FROM quiz_attempts WHERE quiz_id = ? AND user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$quizId, $user['id']]);
$lastAttempt = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="/student/courses.php" class="navbar-brand">
                <i data-lucide="arrow-left"></i>
                <span><?php echo htmlspecialchars($quiz['title']); ?></span>
            </a>
        </div>
    </nav>

    <div style="max-width: 800px; margin: 0 auto; padding: 2rem;">
        <div class="card" style="padding: 2rem;">
            <?php if (isset($_GET['result'])): ?>
                <div style="text-align: center;">
                    <?php if ($_GET['passed']): ?>
                        <div style="color: var(--success); margin-bottom: 1rem;">
                            <i data-lucide="check-circle" style="width: 64px; height: 64px;"></i>
                        </div>
                        <h1 style="color: var(--success);">Congratulations!</h1>
                        <p>You passed the quiz with <?php echo $_GET['score']; ?>/<?php echo $_GET['total']; ?> correct answers</p>
                    <?php else: ?>
                        <div style="color: var(--danger); margin-bottom: 1rem;">
                            <i data-lucide="x-circle" style="width: 64px; height: 64px;"></i>
                        </div>
                        <h1 style="color: var(--danger);">Quiz Not Passed</h1>
                        <p>You scored <?php echo $_GET['score']; ?>/<?php echo $_GET['total']; ?> correct answers</p>
                        <p style="color: var(--text-secondary);">Passing score: <?php echo $quiz['passing_score']; ?>%</p>
                    <?php endif; ?>
                    <a href="/student/courses.php" class="btn btn-primary" style="margin-top: 2rem;">Back to Courses</a>
                </div>
            <?php else: ?>
                <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>
                <p style="color: var(--text-secondary); margin-bottom: 2rem;">Passing score: <?php echo $quiz['passing_score']; ?>%</p>
                
                <form method="POST">
                    <?php foreach ($questions as $index => $question): ?>
                        <div style="margin-bottom: 2rem; padding: 1.5rem; background: var(--bg-tertiary); border-radius: 1rem;">
                            <h3><?php echo ($index + 1) . '. ' . htmlspecialchars($question['question_text']); ?></h3>
                            <?php $options = json_decode($question['options'], true); ?>
                            <?php foreach ($options as $option): ?>
                                <label style="display: block; margin-top: 1rem; cursor: pointer;">
                                    <input type="radio" name="question_<?php echo $question['id']; ?>" value="<?php echo htmlspecialchars($option); ?>" required>
                                    <span style="margin-left: 0.5rem;"><?php echo htmlspecialchars($option); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Quiz</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
