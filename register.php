<?php
require_once 'config/config.php';
require_once 'includes/Auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /student/dashboard.php');
    exit;
}

$error = '';
$success = '';
$step = $_GET['step'] ?? 'register';
$userId = $_SESSION['temp_user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        if ($step === 'register') {
            // Step 1: Register user
            $name = sanitize($_POST['name'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($name) || empty($email) || empty($password)) {
                $error = 'Please fill in all fields';
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match';
            } elseif (strlen($password) < 8) {
                $error = 'Password must be at least 8 characters';
            } else {
                $result = Auth::register($email, $password, $name);
                
                if ($result['success']) {
                    $userId = $result['user_id'];
                    $_SESSION['temp_user_id'] = $userId;
                    
                    // Generate and send OTP
                    $otp = Auth::generateOTP($userId, 'email');
                    Auth::sendOTPEmail($email, $otp, $name);
                    
                    header('Location: /register.php?step=verify');
                    exit;
                } else {
                    $error = $result['message'];
                }
            }
        } elseif ($step === 'verify') {
            // Step 2: Verify OTP
            $otp = sanitize($_POST['otp'] ?? '');
            
            if (empty($otp)) {
                $error = 'Please enter the verification code';
            } elseif (!$userId) {
                $error = 'Session expired. Please register again.';
            } else {
                $result = Auth::verifyOTP($userId, $otp, 'email');
                
                if ($result['success']) {
                    // Auto-login
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['user_role'] = 'student';
                    unset($_SESSION['temp_user_id']);
                    
                    header('Location: /student/dashboard.php');
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
    <title>Create Account - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: var(--spacing-xl);
        }
        .auth-card {
            width: 100%;
            max-width: 450px;
            background: white;
            border-radius: var(--radius-xl);
            padding: var(--spacing-2xl);
            box-shadow: var(--shadow-xl);
        }
        .auth-header {
            text-align: center;
            margin-bottom: var(--spacing-2xl);
        }
        .auth-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-lg);
        }
        .form-group {
            margin-bottom: var(--spacing-lg);
        }
        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: var(--spacing-sm);
        }
        .form-input {
            width: 100%;
            padding: var(--spacing-md);
            border: 2px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: var(--transition);
        }
        .form-input:focus {
            outline: none;
            border-color: var(--primary);
        }
        .alert {
            padding: var(--spacing-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--spacing-lg);
        }
        .alert-error {
            background: rgba(245, 101, 101, 0.1);
            border: 1px solid rgba(245, 101, 101, 0.3);
            color: var(--error);
        }
        .alert-success {
            background: rgba(72, 187, 120, 0.1);
            border: 1px solid rgba(72, 187, 120, 0.3);
            color: var(--success);
        }
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: var(--spacing-xl) 0;
        }
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--border);
        }
        .divider span {
            padding: 0 var(--spacing-md);
            color: var(--muted-foreground);
            font-size: 0.875rem;
        }
        .otp-input {
            width: 100%;
            text-align: center;
            font-size: 2rem;
            letter-spacing: 1rem;
            padding: var(--spacing-lg);
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <a href="/" class="auth-logo">
                    <i data-lucide="book-open" style="width: 32px; height: 32px; color: var(--primary);"></i>
                    <span style="font-size: 1.5rem; font-weight: 700;" class="text-gradient"><?php echo SITE_NAME; ?></span>
                </a>
                <?php if ($step === 'register'): ?>
                    <h2 style="margin-bottom: var(--spacing-sm);">Create Account</h2>
                    <p class="text-muted">Start your learning journey today</p>
                <?php else: ?>
                    <h2 style="margin-bottom: var(--spacing-sm);">Verify Email</h2>
                    <p class="text-muted">Enter the 6-digit code sent to your email</p>
                <?php endif; ?>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if ($step === 'register'): ?>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" id="name" name="name" class="form-input" placeholder="John Doe" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="you@example.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required minlength="8">
                        <small class="text-muted">At least 8 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="••••••••" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Create Account</button>
                </form>
            <?php else: ?>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <div class="form-group">
                        <label for="otp" class="form-label">Verification Code</label>
                        <input type="text" id="otp" name="otp" class="form-input otp-input" placeholder="000000" required maxlength="6" pattern="[0-9]{6}">
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Verify Email</button>
                    
                    <div style="text-align: center; margin-top: var(--spacing-lg);">
                        <p class="text-muted" style="font-size: 0.875rem;">Didn't receive the code?</p>
                        <a href="/api/auth/resend-otp.php" style="font-size: 0.875rem;">Resend Code</a>
                    </div>
                </form>
            <?php endif; ?>
            
            <div class="divider">
                <span>Already have an account?</span>
            </div>
            
            <a href="/login.php" class="btn btn-outline" style="width: 100%;">Sign In</a>
            
            <div style="text-align: center; margin-top: var(--spacing-xl);">
                <a href="/" style="font-size: 0.875rem; color: var(--muted-foreground);">
                    <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                    Back to Home
                </a>
            </div>
        </div>
    </div>
    
    <script>
        lucide.createIcons();
        
        // Auto-format OTP input
        const otpInput = document.getElementById('otp');
        if (otpInput) {
            otpInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
    </script>
</body>
</html>
