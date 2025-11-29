<?php
/**
 * Utility Functions
 */

// Format currency
function formatCurrency($amount) {
    return '৳ ' . number_format($amount, 2);
}

// Format date
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

// Format time ago
function timeAgo($date) {
    $time = strtotime($date);
    $diff = time() - $time;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    if ($diff < 2592000) return floor($diff / 604800) . ' weeks ago';
    
    return date('M d, Y', $time);
}

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Generate random string
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// Generate OTP
function generateOTP($length = 6) {
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= random_int(0, 9);
    }
    return $otp;
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Get base URL
function getBaseURL() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    return $protocol . $_SERVER['HTTP_HOST'];
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('/login.php');
    }
}

// Require role
function requireRole($role) {
    $user = getCurrentUser();
    if (!$user || $user['role'] !== $role) {
        http_response_code(403);
        die('Access Denied');
    }
}

// Get database connection
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    
    return $pdo;
}

// Generate CSRF token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Get CSRF token input
function csrfTokenInput() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

// Pagination
function getPagination($page, $perPage, $total) {
    $totalPages = ceil($total / $perPage);
    $offset = ($page - 1) * $perPage;
    
    return [
        'page' => $page,
        'perPage' => $perPage,
        'offset' => $offset,
        'total' => $total,
        'totalPages' => $totalPages
    ];
}

// Get pagination links
function getPaginationLinks($page, $totalPages, $baseURL) {
    $links = '';
    
    if ($page > 1) {
        $links .= '<a href="' . $baseURL . '?page=1">First</a> ';
        $links .= '<a href="' . $baseURL . '?page=' . ($page - 1) . '">Previous</a> ';
    }
    
    for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++) {
        if ($i == $page) {
            $links .= '<strong>' . $i . '</strong> ';
        } else {
            $links .= '<a href="' . $baseURL . '?page=' . $i . '">' . $i . '</a> ';
        }
    }
    
    if ($page < $totalPages) {
        $links .= '<a href="' . $baseURL . '?page=' . ($page + 1) . '">Next</a> ';
        $links .= '<a href="' . $baseURL . '?page=' . $totalPages . '">Last</a>';
    }
    
    return $links;
}

// Log activity
function logActivity($userId, $action, $details = '') {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, action, details, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$userId, $action, $details]);
}

// Get user role label
function getRoleLabel($role) {
    $labels = [
        'admin' => 'Administrator',
        'instructor' => 'Instructor',
        'student' => 'Student'
    ];
    return $labels[$role] ?? $role;
}

// Calculate course progress
function calculateCourseProgress($courseId, $userId) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total FROM lessons
        WHERE module_id IN (
            SELECT id FROM modules WHERE course_id = ?
        )
    ");
    $stmt->execute([$courseId]);
    $totalLessons = $stmt->fetch()['total'];
    
    if ($totalLessons == 0) return 0;
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as completed FROM lesson_progress
        WHERE user_id = ? AND completed = 1
        AND lesson_id IN (
            SELECT id FROM lessons
            WHERE module_id IN (
                SELECT id FROM modules WHERE course_id = ?
            )
        )
    ");
    $stmt->execute([$userId, $courseId]);
    $completedLessons = $stmt->fetch()['completed'];
    
    return round(($completedLessons / $totalLessons) * 100);
}

// Check if user is enrolled
function isEnrolled($courseId, $userId) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id FROM enrollments WHERE course_id = ? AND user_id = ?");
    $stmt->execute([$courseId, $userId]);
    return $stmt->fetch() !== false;
}

// Send email (placeholder)
function sendEmail($to, $subject, $message) {
    // TODO: Implement with PHPMailer or mail()
    // For now, just log it
    error_log("Email to $to: $subject");
    return true;
}

// Validate password strength
function isStrongPassword($password) {
    return strlen($password) >= 8 &&
           preg_match('/[A-Z]/', $password) &&
           preg_match('/[a-z]/', $password) &&
           preg_match('/[0-9]/', $password);
}

// Get file extension
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

// Validate file upload
function validateFileUpload($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 5242880) {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'error' => 'No file uploaded'];
    }
    
    $ext = getFileExtension($file['name']);
    if (!in_array($ext, $allowedTypes)) {
        return ['success' => false, 'error' => 'File type not allowed'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File size too large'];
    }
    
    return ['success' => true];
}

// Slugify string
function slugify($string) {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

