<?php
require_once '../config/config.php';
requireLogin();
requireRole('instructor');

$user = getCurrentUser();
$courseId = intval($_GET['course_id'] ?? 0);
$pdo = getDBConnection();

$stmt = $pdo->prepare("
    SELECT q.* FROM quizzes q
    JOIN courses c ON q.course_id = c.id
    WHERE c.instructor_id = ? AND q.course_id = ?
    ORDER BY q.created_at DESC
");
$stmt->execute([$user['id'], $courseId]);
$quizzes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Quizzes - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-2xl);">
                <h1>Course Quizzes</h1>
                <a href="/instructor/create-quiz.php?course_id=<?php echo $courseId; ?>" class="btn btn-primary">
                    <i data-lucide="plus"></i> Create Quiz
                </a>
            </div>
            
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Passing Score</th>
                            <th>Time Limit</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quizzes as $quiz): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                                <td><?php echo $quiz['passing_score']; ?>%</td>
                                <td><?php echo $quiz['time_limit_minutes']; ?> min</td>
                                <td><?php echo date('M d, Y', strtotime($quiz['created_at'])); ?></td>
                                <td>
                                    <a href="/instructor/edit-quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-secondary" style="padding: var(--spacing-sm);">
                                        <i data-lucide="edit" style="width: 16px; height: 16px;"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
