<?php

// Common utility functions

function formatCurrency($amount) {
    return '৳' . number_format($amount, 2);
}

function getProgressPercentage($completed, $total) {
    return $total > 0 ? round(($completed / $total) * 100) : 0;
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $current = time();
    $diff = $current - $time;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M d, Y', $time);
}

function truncateText($text, $length = 100) {
    return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
}

function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isValidPhone($phone) {
    return preg_match('/^[0-9]{10,15}$/', preg_replace('/[^0-9]/', '', $phone));
}

function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

function getFileMimeType($filename) {
    $ext = getFileExtension($filename);
    $mimes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'mp4' => 'video/mp4',
        'avi' => 'video/x-msvideo',
        'mov' => 'video/quicktime',
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'zip' => 'application/zip',
    ];
    return $mimes[$ext] ?? 'application/octet-stream';
}

function sanitizeFilename($filename) {
    $filename = basename($filename);
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    return $filename;
}

function createDirectory($path) {
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

function deleteDirectory($path) {
    if (is_dir($path)) {
        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            $filePath = $path . '/' . $file;
            is_dir($filePath) ? deleteDirectory($filePath) : unlink($filePath);
        }
        rmdir($path);
    }
}

function getServerUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'];
}

function redirect($url, $statusCode = 302) {
    header('Location: ' . $url, true, $statusCode);
    exit;
}

function json_response($data, $statusCode = 200) {
    header('Content-Type: application/json', true, $statusCode);
    echo json_encode($data);
    exit;
}

function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

function log_activity($userId, $action, $details = '') {
    $pdo = getDBConnection();
    $ip = get_client_ip();
    $stmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, action, details, ip_address, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$userId, $action, $details, $ip]);
}

function send_notification($userId, $title, $message, $type = 'info') {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, title, message, type, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$userId, $title, $message, $type]);
}

function get_notifications($userId, $limit = 10) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll();
}

function mark_notification_read($notificationId) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    $stmt->execute([$notificationId]);
}
EOF1
<?php

class Middleware {
    
    public static function requireLogin() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }
    
    public static function requireRole($role) {
        self::requireLogin();
        $user = getCurrentUser();
        if ($user['role'] !== $role) {
            header('HTTP/1.0 403 Forbidden');
            exit('Access Denied');
        }
    }
    
    public static function requireRoles($roles) {
        self::requireLogin();
        $user = getCurrentUser();
        if (!in_array($user['role'], $roles)) {
            header('HTTP/1.0 403 Forbidden');
            exit('Access Denied');
        }
    }
    
    public static function verifyCSRF($token) {
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $token) {
            header('HTTP/1.0 403 Forbidden');
            exit('CSRF token validation failed');
        }
    }
    
    public static function rateLimit($action, $limit = 5, $window = 60) {
        $ip = get_client_ip();
        $key = "ratelimit:{$action}:{$ip}";
        
        // Simple rate limiting using file system
        $file = '/tmp/' . md5($key) . '.txt';
        $count = file_exists($file) ? (int)file_get_contents($file) : 0;
        $mtime = file_exists($file) ? filemtime($file) : 0;
        
        if (time() - $mtime > $window) {
            file_put_contents($file, '1');
            return true;
        }
        
        if ($count >= $limit) {
            return false;
        }
        
        file_put_contents($file, $count + 1);
        return true;
    }
    
    public static function validateInput($data, $rules) {
        $errors = [];
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? '';
            
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = ucfirst($field) . ' is required';
            }
            
            if (strpos($rule, 'email') !== false && !isValidEmail($value)) {
                $errors[$field] = 'Invalid email format';
            }
            
            if (strpos($rule, 'phone') !== false && !isValidPhone($value)) {
                $errors[$field] = 'Invalid phone format';
            }
            
            if (preg_match('/min:(\d+)/', $rule, $matches)) {
                if (strlen($value) < $matches[1]) {
                    $errors[$field] = ucfirst($field) . ' must be at least ' . $matches[1] . ' characters';
                }
            }
            
            if (preg_match('/max:(\d+)/', $rule, $matches)) {
                if (strlen($value) > $matches[1]) {
                    $errors[$field] = ucfirst($field) . ' must not exceed ' . $matches[1] . ' characters';
                }
            }
        }
        return $errors;
    }
}
