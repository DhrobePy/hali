<?php
/**
 * Database Configuration for Fajracct LMS
 * 
 * Instructions for cPanel deployment:
 * 1. Create a MySQL database in cPanel
 * 2. Create a database user with all privileges
 * 3. Update the credentials below
 */

// Database credentials - UPDATE THESE FOR YOUR CPANEL
define('DB_HOST', 'localhost');
define('DB_NAME', 'ujjalfmc_hossenaali');
define('DB_USER', 'ujjalfmc_hossenaali');
define('DB_PASS', 'Hossenaali123456789');
define('DB_CHARSET', 'utf8mb4');

// Create PDO connection
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please check your configuration.");
        }
    }
    
    return $pdo;
}

// Test database connection
function testDBConnection() {
    try {
        $pdo = getDBConnection();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
