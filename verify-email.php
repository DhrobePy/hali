<?php
require_once 'config/config.php';

$token = sanitize($_GET['token'] ?? '');

if (!$token) {
    header('Location: /');
    exit;
}

$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify OTP
    $otp = sanitize($_POST['otp']);
    
    $stmt = $pdo->prepare("SELECT * FROM otp_tokens WHERE token = ? AND type = 'email' AND expires_at > NOW() AND used = 0");
    $stmt->execute([$otp]);
    $otpRecord = $stmt->fetch();
    
    if ($otpRecord) {
        // Mark as verified
        $stmt = $pdo->prepare("UPDATE users SET is_verified = 1 WHERE email = ?");
        $stmt->execute([$otpRecord['email']]);
        
        // Mark OTP as used
        $stmt = $pdo->prepare("UPDATE otp_tokens SET used = 1 WHERE id = ?");
        $stmt->execute([$otpRecord['id']]);
        
        header('Location: /login.php?message=verified');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Email - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: var(--spacing-xl);">
        <div class="card" style="max-width: 400px; padding: var(--spacing-2xl);">
            <h1 style="margin-bottom: var(--spacing-lg); text-align: center;">Verify Your Email</h1>
            <p class="text-muted" style="text-align: center; margin-bottom: var(--spacing-xl);">Enter the OTP sent to your email address</p>
            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">OTP Code</label>
                    <input type="text" name="otp" required class="form-input" placeholder="000000" maxlength="6">
                </div>
                
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Verify Email</button>
            </form>
            
            <p style="text-align: center; margin-top: var(--spacing-lg); font-size: 0.875rem;">
                <a href="/login.php" class="text-primary">Back to Login</a>
            </p>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
