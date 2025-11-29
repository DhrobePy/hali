<?php
require_once '../config/config.php';
requireLogin();
requireRole('instructor');

$user = getCurrentUser();
$pdo = getDBConnection();

$stmt = $pdo->prepare("
    SELECT DISTINCT u.* FROM users u
    JOIN enrollments e ON u.id = e.user_id
    JOIN courses c ON e.course_id = c.id
    WHERE c.instructor_id = ?
    ORDER BY u.name ASC
");
$stmt->execute([$user['id']]);
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Students - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <h1>My Students</h1>
            <p class="text-muted">Total: <?php echo count($students); ?> students enrolled</p>
            
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Enrolled Courses</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td>
                                    <?php
                                    $stmt = $pdo->prepare("
                                        SELECT COUNT(*) FROM enrollments e
                                        JOIN courses c ON e.course_id = c.id
                                        WHERE e.user_id = ? AND c.instructor_id = ?
                                    ");
                                    $stmt->execute([$student['id'], $user['id']]);
                                    echo $stmt->fetch()[0];
                                    ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
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
