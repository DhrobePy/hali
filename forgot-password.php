<?php
require_once 'config/config.php';

if (isLoggedIn()) {
    redirect('/');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    
    if (!$email) {
        $error = 'Email is required';
    } elseif (!isValidEmail($email)) {
        $error = 'Invalid email address';
    } else {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            $token = generateRandomString(32);
            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?");
            $stmt->execute([$token, $email]);
            
            $resetLink = getBaseURL() . "/reset-password.php?token=$token";
            sendEmail($email, 'Password Reset Request', "Click here to reset your password: $resetLink");
            
            $success = 'Password reset link sent to your email';
        } else {
            $success = 'If an account exists with this email, a reset link has been sent';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #6366f1 0%, #ec4899 100%);">
        <div class="card" style="width: 100%; max-width: 400px; padding: 2rem;">
            <h1 style="text-align: center; margin-bottom: 1rem;">Forgot Password</h1>
            <p style="text-align: center; color: var(--text-secondary); margin-bottom: 1.5rem;">Enter your email to receive a password reset link</p>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-input" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Send Reset Link</button>
            </form>
            
            <p style="text-align: center; margin-top: 1rem; color: var(--text-secondary);">
                Remember your password? <a href="/login.php" style="color: var(--primary); text-decoration: none;">Login</a>
            </p>
        </div>
    </div>
</body>
</html>
