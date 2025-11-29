<?php

class Email {
    
    public static function sendWelcomeEmail($email, $name) {
        return self::send($email, 'Welcome to ' . SITE_NAME, self::getWelcomeTemplate($name));
    }
    
    public static function sendOTPEmail($email, $otp, $name = '') {
        return self::send($email, 'Your OTP Code', self::getOTPTemplate($otp, $name));
    }
    
    public static function sendPasswordResetEmail($email, $resetLink) {
        return self::send($email, 'Reset Your Password', self::getPasswordResetTemplate($resetLink));
    }
    
    public static function sendEnrollmentConfirmation($email, $courseName) {
        return self::send($email, 'Enrollment Confirmation', self::getEnrollmentTemplate($courseName));
    }
    
    public static function sendCertificateEmail($email, $name, $courseName, $certificateId) {
        return self::send($email, 'Your Certificate', self::getCertificateTemplate($name, $courseName, $certificateId));
    }
    
    public static function sendCourseUpdateEmail($email, $courseName, $updateMessage) {
        return self::send($email, 'Course Update: ' . $courseName, self::getCourseUpdateTemplate($courseName, $updateMessage));
    }
    
    private static function send($to, $subject, $body) {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . SITE_NAME . " <" . SUPPORT_EMAIL . ">\r\n";
        
        return mail($to, $subject, $body, $headers);
    }
    
    private static function getWelcomeTemplate($name) {
        return "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Welcome to " . SITE_NAME . "!</h2>
                <p>Hi {$name},</p>
                <p>Thank you for joining us. We're excited to have you on board!</p>
                <p>Start learning today and expand your skills.</p>
                <p><a href='" . SITE_URL . "/login.php' style='background: #6366f1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Login to Dashboard</a></p>
                <p>Best regards,<br>" . SITE_NAME . " Team</p>
            </body>
            </html>
        ";
    }
    
    private static function getOTPTemplate($otp, $name = '') {
        return "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Your OTP Code</h2>
                <p>Hi {$name},</p>
                <p>Your One-Time Password (OTP) is:</p>
                <h1 style='color: #6366f1; letter-spacing: 5px;'>{$otp}</h1>
                <p>This code will expire in 10 minutes.</p>
                <p>If you didn't request this, please ignore this email.</p>
                <p>Best regards,<br>" . SITE_NAME . " Team</p>
            </body>
            </html>
        ";
    }
    
    private static function getPasswordResetTemplate($resetLink) {
        return "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Reset Your Password</h2>
                <p>We received a request to reset your password.</p>
                <p><a href='{$resetLink}' style='background: #6366f1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Reset Password</a></p>
                <p>This link will expire in 1 hour.</p>
                <p>If you didn't request this, please ignore this email.</p>
                <p>Best regards,<br>" . SITE_NAME . " Team</p>
            </body>
            </html>
        ";
    }
    
    private static function getEnrollmentTemplate($courseName) {
        return "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Enrollment Confirmed!</h2>
                <p>You have successfully enrolled in <strong>{$courseName}</strong></p>
                <p><a href='" . SITE_URL . "/student/courses.php' style='background: #6366f1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Start Learning</a></p>
                <p>Best regards,<br>" . SITE_NAME . " Team</p>
            </body>
            </html>
        ";
    }
    
    private static function getCertificateTemplate($name, $courseName, $certificateId) {
        return "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Congratulations!</h2>
                <p>Hi {$name},</p>
                <p>You have successfully completed <strong>{$courseName}</strong></p>
                <p>Your certificate is ready to download.</p>
                <p><a href='" . SITE_URL . "/api/certificates/download.php?id={$certificateId}' style='background: #6366f1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Download Certificate</a></p>
                <p>Best regards,<br>" . SITE_NAME . " Team</p>
            </body>
            </html>
        ";
    }
    
    private static function getCourseUpdateTemplate($courseName, $updateMessage) {
        return "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Course Update: {$courseName}</h2>
                <p>{$updateMessage}</p>
                <p><a href='" . SITE_URL . "/student/courses.php' style='background: #6366f1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>View Course</a></p>
                <p>Best regards,<br>" . SITE_NAME . " Team</p>
            </body>
            </html>
        ";
    }
}
