<?php
/**
 * Main Configuration File for Fajracct LMS
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Site Configuration
define('SITE_NAME', 'H. Ali');
define('SITE_URL', 'https://hossenaali.ujjalfm.com'); // UPDATE THIS
define('SITE_EMAIL', 'hossenaali@ujjalfm.com'); // UPDATE THIS

// Directory paths
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('ASSETS_PATH', ROOT_PATH . '/assets');

// URL paths
define('BASE_URL', SITE_URL);
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOAD_URL', BASE_URL . '/uploads');

// Security settings
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_COST', 12);
define('SESSION_LIFETIME', 3600 * 24 * 7); // 7 days
define('OTP_EXPIRY_MINUTES', 10);
define('OTP_LENGTH', 6);

// Pagination
define('ITEMS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// File upload settings
define('MAX_FILE_SIZE', 100 * 1024 * 1024); // 100MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/webm']);

// Email configuration (for OTP and notifications)
// Using PHP mail() function by default
// For production, integrate with SendGrid or Mailgun
define('SMTP_HOST', 'mail.ujjalfm.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'hossenaali@ujjalfm.com');
define('SMTP_PASS', 'Hossenaali123456789');
define('SMTP_ENCRYPTION', 'tls');

// SMS configuration (for OTP)
// Integrate with Bangladeshi SMS gateway
define('SMS_API_URL', '');
define('SMS_API_KEY', '');

// Payment gateway configuration
define('BKASH_APP_KEY', '');
define('BKASH_APP_SECRET', '');
define('BKASH_USERNAME', '');
define('BKASH_PASSWORD', '');
define('BKASH_BASE_URL', 'https://checkout.sandbox.bkash.com'); // Use production URL for live

define('NAGAD_MERCHANT_ID', '');
define('NAGAD_MERCHANT_NUMBER', '');
define('NAGAD_PUBLIC_KEY', '');
define('NAGAD_PRIVATE_KEY', '');
define('NAGAD_BASE_URL', 'http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0'); // Use production URL for live

// Video hosting configuration
define('VIMEO_ACCESS_TOKEN', '');
define('VIMEO_CLIENT_ID', '');
define('VIMEO_CLIENT_SECRET', '');

define('BUNNY_API_KEY', '');
define('BUNNY_LIBRARY_ID', '');
define('BUNNY_STREAM_URL', '');

// Google OAuth configuration
define('GOOGLE_CLIENT_ID', '');
define('GOOGLE_CLIENT_SECRET', '');
define('GOOGLE_REDIRECT_URI', BASE_URL . '/api/auth/google-callback.php');

// Timezone
date_default_timezone_set('Asia/Dhaka');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once __DIR__ . '/database.php';

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to get current user
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Helper function to check user role
function hasRole($role) {
    $user = getCurrentUser();
    return $user && $user['role'] === $role;
}

// Helper function to require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit;
    }
}

// Helper function to require specific role
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: /403.php');
        exit;
    }
}

// Helper function to sanitize input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Helper function to generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Helper function to verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Helper function to format currency
function formatCurrency($amount) {
    return '৳' . number_format($amount / 100, 2);
}

// Helper function to generate random string
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}
