<?php

class Certificate {
    
    public static function generateCertificate($userId, $courseId) {
        $pdo = getDBConnection();
        
        // Get user and course info
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$courseId]);
        $course = $stmt->fetch();
        
        if (!$user || !$course) {
            return false;
        }
        
        // Check if already has certificate
        $stmt = $pdo->prepare("SELECT * FROM certificates WHERE user_id = ? AND course_id = ?");
        $stmt->execute([$userId, $courseId]);
        if ($stmt->fetch()) {
            return true; // Already exists
        }
        
        // Create certificate
        $certificateId = 'CERT-' . time() . '-' . rand(10000, 99999);
        
        $stmt = $pdo->prepare("
            INSERT INTO certificates (certificate_id, user_id, course_id, course_title, issued_at, created_at)
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$certificateId, $userId, $courseId, $course['title']]);
        
        return $certificateId;
    }
    
    public static function getCertificate($certificateId) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM certificates WHERE certificate_id = ?");
        $stmt->execute([$certificateId]);
        return $stmt->fetch();
    }
    
    public static function verifyCertificate($certificateId) {
        $cert = self::getCertificate($certificateId);
        return $cert ? true : false;
    }
    
    public static function generatePDF($certificateId) {
        $cert = self::getCertificate($certificateId);
        
        if (!$cert) {
            return false;
        }
        
        // In production, use FPDF or similar library
        // For now, return placeholder
        return [
            'success' => true,
            'filename' => 'certificate-' . $certificateId . '.pdf',
            'content' => 'PDF content would be generated here'
        ];
    }
    
    public static function getUserCertificates($userId) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            SELECT * FROM certificates 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
