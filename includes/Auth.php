<?php
/**
 * Authentication Helper Class
 */

class Auth {
    
    /**
     * Register a new user
     */
    public static function register($email, $password, $name, $role = 'student') {
        $pdo = getDBConnection();
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email already registered'];
        }
        
        // Hash password
        $passwordHash = password_hash($password, PASSWORD_HASH_ALGO, ['cost' => PASSWORD_HASH_COST]);
        
        // Insert user
        $stmt = $pdo->prepare("
            INSERT INTO users (email, password_hash, name, role, login_method) 
            VALUES (?, ?, ?, ?, 'email')
        ");
        
        try {
            $stmt->execute([$email, $passwordHash, $name, $role]);
            $userId = $pdo->lastInsertId();
            
            return ['success' => true, 'user_id' => $userId];
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }
    
    /**
     * Login user
     */
    public static function login($email, $password, $rememberMe = false) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        // Check if email is verified
        if (!$user['email_verified']) {
            return ['success' => false, 'message' => 'Please verify your email first', 'user_id' => $user['id']];
        }
        
        // Update last signed in
        $stmt = $pdo->prepare("UPDATE users SET last_signed_in = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        
        // Set remember me cookie if requested
        if ($rememberMe) {
            $token = generateRandomString(64);
            setcookie('remember_token', $token, time() + SESSION_LIFETIME, '/', '', true, true);
            // TODO: Store token in database for validation
        }
        
        return ['success' => true, 'user' => $user];
    }
    
    /**
     * Logout user
     */
    public static function logout() {
        session_destroy();
        setcookie('remember_token', '', time() - 3600, '/');
        return ['success' => true];
    }
    
    /**
     * Generate OTP
     */
    public static function generateOTP($userId, $type = 'email') {
        $pdo = getDBConnection();
        
        // Generate random OTP
        $otp = str_pad(random_int(0, 999999), OTP_LENGTH, '0', STR_PAD_LEFT);
        
        // Calculate expiry time
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
        
        // Store OTP
        $stmt = $pdo->prepare("
            INSERT INTO otp_tokens (user_id, token, type, expires_at) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $otp, $type, $expiresAt]);
        
        return $otp;
    }
    
    /**
     * Verify OTP
     */
    public static function verifyOTP($userId, $otp, $type = 'email') {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            SELECT * FROM otp_tokens 
            WHERE user_id = ? AND token = ? AND type = ? 
            AND verified = FALSE AND expires_at > NOW()
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$userId, $otp, $type]);
        $token = $stmt->fetch();
        
        if (!$token) {
            return ['success' => false, 'message' => 'Invalid or expired OTP'];
        }
        
        // Mark as verified
        $stmt = $pdo->prepare("UPDATE otp_tokens SET verified = TRUE WHERE id = ?");
        $stmt->execute([$token['id']]);
        
        // Update user verification status
        if ($type === 'email') {
            $stmt = $pdo->prepare("UPDATE users SET email_verified = TRUE WHERE id = ?");
            $stmt->execute([$userId]);
        } elseif ($type === 'phone') {
            $stmt = $pdo->prepare("UPDATE users SET phone_verified = TRUE WHERE id = ?");
            $stmt->execute([$userId]);
        }
        
        return ['success' => true];
    }
    
    /**
     * Send OTP email
     */
    public static function sendOTPEmail($email, $otp, $name = '') {
        $subject = "Your Verification Code - " . SITE_NAME;
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .otp-code { background: white; border: 2px dashed #667eea; padding: 20px; text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 8px; margin: 20px 0; border-radius: 8px; color: #667eea; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>" . SITE_NAME . "</h1>
                </div>
                <div class='content'>
                    <h2>Email Verification</h2>
                    <p>Hello" . ($name ? " $name" : "") . ",</p>
                    <p>Your verification code is:</p>
                    <div class='otp-code'>$otp</div>
                    <p>This code will expire in " . OTP_EXPIRY_MINUTES . " minutes.</p>
                    <p>If you didn't request this code, please ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Send email using PHP mail() function
        // For production, use PHPMailer or integrate with SendGrid/Mailgun
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . SITE_NAME . " <" . SITE_EMAIL . ">" . "\r\n";
        
        $sent = mail($email, $subject, $html, $headers);
        
        // Log for development
        error_log("OTP Email sent to $email: $otp");
        
        return $sent;
    }
    
    /**
     * Send OTP SMS
     */
    public static function sendOTPSMS($phoneNumber, $otp) {
        $message = "Your " . SITE_NAME . " verification code is: $otp. Valid for " . OTP_EXPIRY_MINUTES . " minutes.";
        
        // TODO: Integrate with SMS gateway (MiMSMS, Khudebarta, etc.)
        // Example implementation:
        // $apiUrl = SMS_API_URL;
        // $apiKey = SMS_API_KEY;
        // $response = file_get_contents($apiUrl . '?api_key=' . $apiKey . '&to=' . $phoneNumber . '&message=' . urlencode($message));
        
        // Log for development
        error_log("OTP SMS sent to $phoneNumber: $otp");
        
        return true;
    }
    
    /**
     * Request password reset
     */
    public static function requestPasswordReset($email) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // Don't reveal if email exists for security
            return ['success' => true, 'message' => 'If the email exists, a reset code has been sent'];
        }
        
        // Generate OTP
        $otp = self::generateOTP($user['id'], 'password_reset');
        
        // Send email
        self::sendOTPEmail($email, $otp, $user['name']);
        
        return ['success' => true, 'message' => 'If the email exists, a reset code has been sent'];
    }
    
    /**
     * Reset password with OTP
     */
    public static function resetPassword($email, $otp, $newPassword) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid request'];
        }
        
        // Verify OTP
        $result = self::verifyOTP($user['id'], $otp, 'password_reset');
        
        if (!$result['success']) {
            return $result;
        }
        
        // Update password
        $passwordHash = password_hash($newPassword, PASSWORD_HASH_ALGO, ['cost' => PASSWORD_HASH_COST]);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$passwordHash, $user['id']]);
        
        return ['success' => true, 'message' => 'Password reset successfully'];
    }
    
    /**
     * Google OAuth login
     */
    public static function googleLogin($googleUser) {
        $pdo = getDBConnection();
        
        // Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR open_id = ?");
        $stmt->execute([$googleUser['email'], $googleUser['id']]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Update user info
            $stmt = $pdo->prepare("
                UPDATE users 
                SET open_id = ?, name = ?, avatar_url = ?, email_verified = TRUE, last_signed_in = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$googleUser['id'], $googleUser['name'], $googleUser['picture'], $user['id']]);
        } else {
            // Create new user
            $stmt = $pdo->prepare("
                INSERT INTO users (open_id, email, name, avatar_url, login_method, email_verified) 
                VALUES (?, ?, ?, ?, 'google', TRUE)
            ");
            $stmt->execute([$googleUser['id'], $googleUser['email'], $googleUser['name'], $googleUser['picture']]);
            $userId = $pdo->lastInsertId();
            
            // Fetch the new user
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        
        return ['success' => true, 'user' => $user];
    }
}
