<?php

class Notification {
    
    public static function send($userId, $title, $message, $type = 'info') {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, title, message, type, is_read, created_at)
            VALUES (?, ?, ?, ?, 0, NOW())
        ");
        $stmt->execute([$userId, $title, $message, $type]);
        
        return true;
    }
    
    public static function sendToRole($role, $title, $message, $type = 'info') {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE role = ?");
        $stmt->execute([$role]);
        $users = $stmt->fetchAll();
        
        foreach ($users as $user) {
            self::send($user['id'], $title, $message, $type);
        }
        
        return true;
    }
    
    public static function getUnread($userId) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            SELECT * FROM notifications
            WHERE user_id = ? AND is_read = 0
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public static function markAsRead($notificationId) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        $stmt->execute([$notificationId]);
        
        return true;
    }
    
    public static function markAllAsRead($userId) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        return true;
    }
}
