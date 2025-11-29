<?php
require_once '../../config/config.php';
requireLogin();

$user = getCurrentUser();
$quizId = intval($_GET['quiz'] ?? 0);
$pdo = getDBConnection();

// Get quiz
$stmt = $pdo->prepare("SELECT q.*, c.id as course_id FROM quizzes q JOIN courses c ON q.course_id = c.id WHERE q.id = ?");
$stmt->execute([$quizId]);
$quiz = $stmt->fetch();

if (!$quiz) {
    header('Location: /student/dashboard.php');
    exit;
}

// Check enrollment
$stmt = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
$stmt->execute([$user['id'], $quiz['course_id']]);
if (!$stmt->fetch()) {
    header('Location: /student/dashboard.php');
    exit;
}

// Get questions
$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY question_order ASC");
$stmt->execute([$quizId]);
$questions = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = 0;
    $totalQuestions = count($questions);
    
    foreach ($questions as $question) {
        $userAnswer = sanitize($_POST['question_' . $question['id']] ?? '');
        if ($userAnswer === $question['correct_answer']) {
            $score++;
        }
    }
    
    $percentage = ($totalQuestions > 0) ? ($score / $totalQuestions) * 100 : 0;
    $passed = $percentage >= $quiz['passing_score'];
    
    $stmt = $pdo->prepare("
        INSERT INTO quiz_attempts (quiz_id, user_id, score, percentage, passed, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$quizId, $user['id'], $score, $percentage, $passed ? 1 : 0]);
    
    header('Location: /student/quiz.php?quiz=' . $quizId . '&result=submitted');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($quiz['title']); ?> - Quiz</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <nav>
                <ul class="sidebar-nav">
                    <li><a href="/student/dashboard.php"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
                    <li><a href="/student/courses.php"><i data-lucide="book-open"></i> My Courses</a></li>
                    <li><a href="/logout.php"><i data-lucide="log-out"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>
            <p class="text-muted">Passing Score: <?php echo $quiz['passing_score']; ?>% | Time Limit: <?php echo $quiz['time_limit_minutes']; ?> minutes</p>
            
            <form method="POST" class="card" style="padding: var(--spacing-2xl); max-width: 800px;">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <?php foreach ($questions as $index => $question): ?>
                    <div style="margin-bottom: var(--spacing-2xl); padding-bottom: var(--spacing-2xl); border-bottom: 1px solid var(--border);">
                        <h3 style="margin-bottom: var(--spacing-lg);">Question <?php echo $index + 1; ?>: <?php echo htmlspecialchars($question['question_text']); ?></h3>
                        
                        <?php
                        $options = json_decode($question['options'], true);
                        foreach ($options as $option):
                        ?>
                            <label style="display: flex; align-items: center; margin-bottom: var(--spacing-md); cursor: pointer;">
                                <input type="radio" name="question_<?php echo $question['id']; ?>" value="<?php echo htmlspecialchars($option); ?>" required>
                                <span style="margin-left: var(--spacing-sm);"><?php echo htmlspecialchars($option); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
                
                <button type="submit" class="btn btn-primary">Submit Quiz</button>
            </form>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
EOF1
<?php
require_once '../../config/config.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDBConnection();

$stmt = $pdo->prepare("
    SELECT c.* FROM certificates c
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$user['id']]);
$certificates = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Certificates - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <nav>
                <ul class="sidebar-nav">
                    <li><a href="/student/dashboard.php"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
                    <li><a href="/student/certificates.php" class="active"><i data-lucide="award"></i> Certificates</a></li>
                    <li><a href="/logout.php"><i data-lucide="log-out"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <h1>My Certificates</h1>
            
            <?php if (count($certificates) > 0): ?>
                <div class="course-grid">
                    <?php foreach ($certificates as $cert): ?>
                        <div class="card" style="padding: var(--spacing-xl); text-align: center;">
                            <i data-lucide="award" style="width: 48px; height: 48px; color: var(--primary); margin: 0 auto var(--spacing-lg);"></i>
                            <h3><?php echo htmlspecialchars($cert['course_title']); ?></h3>
                            <p class="text-muted">Completed on <?php echo date('M d, Y', strtotime($cert['created_at'])); ?></p>
                            <a href="/api/certificates/download.php?id=<?php echo $cert['id']; ?>" class="btn btn-primary" style="margin-top: var(--spacing-lg);">Download Certificate</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card" style="padding: var(--spacing-2xl); text-align: center;">
                    <i data-lucide="award" style="width: 64px; height: 64px; color: var(--muted-foreground); margin: 0 auto var(--spacing-lg);"></i>
                    <p class="text-muted">No certificates yet. Complete a course to earn one!</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
