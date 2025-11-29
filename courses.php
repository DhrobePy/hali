<?php
require_once 'config/config.php';
require_once 'includes/Auth.php';

$step = $_GET['step'] ?? 'request';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } else {
        if ($step === 'request') {
            $email = sanitize($_POST['email'] ?? '');
            if (empty($email)) {
                $error = 'Please enter your email';
            } else {
                $result = Auth::requestPasswordReset($email);
                if ($result['success']) {
                    header('Location: /forgot-password.php?step=verify&email=' . urlencode($email));
                    exit;
                } else {
                    $error = $result['message'];
                }
            }
        } elseif ($step === 'verify') {
            $email = sanitize($_POST['email'] ?? '');
            $otp = sanitize($_POST['otp'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (empty($otp) || empty($password)) {
                $error = 'Please fill all fields';
            } else {
                $result = Auth::resetPassword($email, $otp, $password);
                if ($result['success']) {
                    header('Location: /login.php?message=password_reset_success');
                    exit;
                } else {
                    $error = $result['message'];
                }
            }
        }
    }
}
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h2><?php echo $step === 'request' ? 'Forgot Password' : 'Reset Password'; ?></h2>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <?php if ($step === 'request'): ?>
                    <input type="email" name="email" placeholder="Email" required class="form-input">
                    <button type="submit" class="btn btn-primary">Send Reset Code</button>
                <?php else: ?>
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
                    <input type="text" name="otp" placeholder="6-digit code" required class="form-input">
                    <input type="password" name="password" placeholder="New password" required class="form-input">
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
EOF1
<?php
require_once 'config/config.php';
require_once 'includes/Course.php';

$pdo = getDBConnection();
$stmt = $pdo->query("SELECT c.*, u.name as instructor_name FROM courses c JOIN users u ON c.instructor_id = u.id WHERE c.status = 'published' ORDER BY c.created_at DESC");
$courses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Courses - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <nav class="navbar">
        <div class="container navbar-container">
            <a href="/" class="navbar-brand">
                <i data-lucide="book-open"></i>
                <span class="text-gradient"><?php echo SITE_NAME; ?></span>
            </a>
            <div>
                <a href="/login.php" class="btn btn-secondary">Sign In</a>
            </div>
        </div>
    </nav>
    <section class="section">
        <div class="container">
            <h1>All Courses</h1>
            <div class="course-grid">
                <?php foreach ($courses as $course): ?>
                    <div class="course-card">
                        <img src="<?php echo htmlspecialchars($course['thumbnail_url'] ?? '/assets/images/placeholder.png'); ?>" alt="" class="course-thumbnail">
                        <div class="course-content">
                            <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                            <p class="text-muted"><?php echo htmlspecialchars($course['instructor_name']); ?></p>
                            <a href="/course.php?slug=<?php echo urlencode($course['slug']); ?>" class="btn btn-primary">View Course</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <script>lucide.createIcons();</script>
</body>
</html>
