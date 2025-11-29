<?php

class Payment {
    
    const BKASH_API_URL = 'https://api.bkash.com';
    const NAGAD_API_URL = 'https://api.nagad.com';
    
    public static function initiateBkashPayment($amount, $orderId, $description) {
        $pdo = getDBConnection();
        
        $transactionId = 'BKH-' . time() . '-' . rand(1000, 9999);
        
        $stmt = $pdo->prepare("
            INSERT INTO transactions (transaction_id, order_id, amount, gateway, status, created_at)
            VALUES (?, ?, ?, 'bkash', 'pending', NOW())
        ");
        $stmt->execute([$transactionId, $orderId, $amount]);
        
        // In production, call actual bKash API
        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'redirect_url' => self::BKASH_API_URL . '/payment?txn=' . $transactionId
        ];
    }
    
    public static function initiateNagadPayment($amount, $orderId, $description) {
        $pdo = getDBConnection();
        
        $transactionId = 'NGD-' . time() . '-' . rand(1000, 9999);
        
        $stmt = $pdo->prepare("
            INSERT INTO transactions (transaction_id, order_id, amount, gateway, status, created_at)
            VALUES (?, ?, ?, 'nagad', 'pending', NOW())
        ");
        $stmt->execute([$transactionId, $orderId, $amount]);
        
        // In production, call actual Nagad API
        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'redirect_url' => self::NAGAD_API_URL . '/payment?txn=' . $transactionId
        ];
    }
    
    public static function verifyPayment($transactionId) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM transactions WHERE transaction_id = ?");
        $stmt->execute([$transactionId]);
        $transaction = $stmt->fetch();
        
        if (!$transaction) {
            return ['success' => false, 'error' => 'Transaction not found'];
        }
        
        // In production, verify with payment gateway
        return [
            'success' => true,
            'transaction' => $transaction
        ];
    }
    
    public static function completePayment($transactionId) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("UPDATE transactions SET status = 'completed' WHERE transaction_id = ?");
        $stmt->execute([$transactionId]);
        
        // Get associated subscription
        $stmt = $pdo->prepare("SELECT * FROM transactions WHERE transaction_id = ?");
        $stmt->execute([$transactionId]);
        $transaction = $stmt->fetch();
        
        if ($transaction) {
            // Create subscription
            $stmt = $pdo->prepare("
                INSERT INTO subscriptions (user_id, plan_id, transaction_id, status, starts_at, expires_at, created_at)
                VALUES (?, ?, ?, 'active', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), NOW())
            ");
            $stmt->execute([$transaction['user_id'], $transaction['plan_id'], $transactionId]);
        }
        
        return true;
    }
    
    public static function refundPayment($transactionId, $reason = '') {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("UPDATE transactions SET status = 'refunded', refund_reason = ? WHERE transaction_id = ?");
        $stmt->execute([$reason, $transactionId]);
        
        return true;
    }
    
    public static function getTransactionHistory($userId, $limit = 20) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            SELECT * FROM transactions 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
}
