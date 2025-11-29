<?php
require_once 'config/config.php';
require_once 'includes/Auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $user = getCurrentUser();
    if ($user['role'] === 'admin') {
        header('Location: /admin/dashboard.php');
    } elseif ($user['role'] === 'instructor') {
        header('Location: /instructor/dashboard.php');
    } else {
        header('Location: /student/dashboard.php');
    }
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);
        
        if (empty($email) || empty($password)) {
            $error = 'Please fill in all fields';
        } else {
            $result = Auth::login($email, $password, $rememberMe);
            
            if ($result['success']) {
                // Redirect based on role
                $user = $result['user'];
                if ($user['role'] === 'admin') {
                    header('Location: /admin/dashboard.php');
                } elseif ($user['role'] === 'instructor') {
                    header('Location: /instructor/dashboard.php');
                } else {
                    header('Location: /student/dashboard.php');
                }
                exit;
            } else {
                $error = $result['message'];
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
    <title>Sign In - <?php echo SITE_NAME; ?></title>
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
        .form-checkbox {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
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
                <h2 style="margin-bottom: var(--spacing-sm);">Welcome Back</h2>
                <p class="text-muted">Sign in to continue your learning journey</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input" placeholder="you@example.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
                </div>
                
                <div class="form-group" style="display: flex; align-items: center; justify-content: space-between;">
                    <label class="form-checkbox">
                        <input type="checkbox" name="remember_me">
                        <span>Remember me</span>
                    </label>
                    <a href="/forgot-password.php" style="font-size: 0.875rem;">Forgot password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Sign In</button>
            </form>
            
            <div class="divider">
                <span>Don't have an account?</span>
            </div>
            
            <a href="/register.php" class="btn btn-outline" style="width: 100%;">Create Account</a>
            
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
    </script>
</body>
</html>
