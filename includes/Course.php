<?php
/**
 * Course Management Helper Class
 */

class Course {
    
    /**
     * Get all published courses
     */
    public static function getAllPublished($limit = null, $offset = 0) {
        $pdo = getDBConnection();
        
        $sql = "
            SELECT c.*, u.name as instructor_name, cat.name as category_name
            FROM courses c
            LEFT JOIN users u ON c.instructor_id = u.id
            LEFT JOIN categories cat ON c.category_id = cat.id
            WHERE c.is_published = TRUE
            ORDER BY c.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$limit, $offset]);
        } else {
            $stmt = $pdo->query($sql);
        }
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get featured courses
     */
    public static function getFeatured($limit = 6) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            SELECT c.*, u.name as instructor_name, cat.name as category_name
            FROM courses c
            LEFT JOIN users u ON c.instructor_id = u.id
            LEFT JOIN categories cat ON c.category_id = cat.id
            WHERE c.is_published = TRUE AND c.is_featured = TRUE
            ORDER BY c.enrollment_count DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get course by ID
     */
    public static function getById($id) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            SELECT c.*, u.name as instructor_name, u.bio as instructor_bio, u.avatar_url as instructor_avatar,
                   cat.name as category_name
            FROM courses c
            LEFT JOIN users u ON c.instructor_id = u.id
            LEFT JOIN categories cat ON c.category_id = cat.id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        
        return $stmt->fetch();
    }
    
    /**
     * Get course by slug
     */
    public static function getBySlug($slug) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            SELECT c.*, u.name as instructor_name, u.bio as instructor_bio, u.avatar_url as instructor_avatar,
                   cat.name as category_name
            FROM courses c
            LEFT JOIN users u ON c.instructor_id = u.id
            LEFT JOIN categories cat ON c.category_id = cat.id
            WHERE c.slug = ?
        ");
        $stmt->execute([$slug]);
        
        return $stmt->fetch();
    }
    
    /**
     * Get course modules with lessons
     */
    public static function getModulesWithLessons($courseId) {
        $pdo = getDBConnection();
        
        // Get modules
        $stmt = $pdo->prepare("
            SELECT * FROM modules 
            WHERE course_id = ? 
            ORDER BY order_index ASC
        ");
        $stmt->execute([$courseId]);
        $modules = $stmt->fetchAll();
        
        // Get lessons for each module
        foreach ($modules as &$module) {
            $stmt = $pdo->prepare("
                SELECT * FROM lessons 
                WHERE module_id = ? 
                ORDER BY order_index ASC
            ");
            $stmt->execute([$module['id']]);
            $module['lessons'] = $stmt->fetchAll();
        }
        
        return $modules;
    }
    
    /**
     * Check if user has access to course
     */
    public static function hasAccess($userId, $courseId) {
        $pdo = getDBConnection();
        
        // Check if user has active subscription
        $stmt = $pdo->prepare("
            SELECT s.* FROM subscriptions s
            LEFT JOIN subscription_plans sp ON s.plan_id = sp.id
            WHERE s.user_id = ? 
            AND s.status = 'active'
            AND (s.end_date IS NULL OR s.end_date > NOW())
            AND (s.course_id IS NULL OR s.course_id = ?)
        ");
        $stmt->execute([$userId, $courseId]);
        
        return $stmt->fetch() !== false;
    }
    
    /**
     * Enroll user in course
     */
    public static function enroll($userId, $courseId, $subscriptionId = null) {
        $pdo = getDBConnection();
        
        // Check if already enrolled
        $stmt = $pdo->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
        $stmt->execute([$userId, $courseId]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Already enrolled in this course'];
        }
        
        // Check if user has access
        if (!self::hasAccess($userId, $courseId)) {
            return ['success' => false, 'message' => 'You need an active subscription to enroll'];
        }
        
        // Enroll user
        $stmt = $pdo->prepare("
            INSERT INTO enrollments (user_id, course_id, subscription_id, status, progress) 
            VALUES (?, ?, ?, 'active', 0)
        ");
        
        try {
            $stmt->execute([$userId, $courseId, $subscriptionId]);
            
            // Update enrollment count
            $stmt = $pdo->prepare("UPDATE courses SET enrollment_count = enrollment_count + 1 WHERE id = ?");
            $stmt->execute([$courseId]);
            
            return ['success' => true, 'enrollment_id' => $pdo->lastInsertId()];
        } catch (PDOException $e) {
            error_log("Enrollment error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Enrollment failed'];
        }
    }
    
    /**
     * Get user's enrollments
     */
    public static function getUserEnrollments($userId) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            SELECT e.*, c.title, c.slug, c.thumbnail_url, c.level, 
                   u.name as instructor_name
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            JOIN users u ON c.instructor_id = u.id
            WHERE e.user_id = ?
            ORDER BY e.enrolled_at DESC
        ");
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Update lesson progress
     */
    public static function updateLessonProgress($userId, $lessonId, $watchedDuration, $lastPosition, $completed = false) {
        $pdo = getDBConnection();
        
        // Check if progress exists
        $stmt = $pdo->prepare("SELECT id FROM lesson_progress WHERE user_id = ? AND lesson_id = ?");
        $stmt->execute([$userId, $lessonId]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update existing progress
            $sql = "UPDATE lesson_progress SET watched_duration = ?, last_position = ?";
            $params = [$watchedDuration, $lastPosition];
            
            if ($completed) {
                $sql .= ", completed = TRUE, completed_at = NOW()";
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $existing['id'];
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        } else {
            // Insert new progress
            $stmt = $pdo->prepare("
                INSERT INTO lesson_progress (user_id, lesson_id, watched_duration, last_position, completed, completed_at) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId, 
                $lessonId, 
                $watchedDuration, 
                $lastPosition, 
                $completed, 
                $completed ? date('Y-m-d H:i:s') : null
            ]);
        }
        
        // Update course progress
        self::updateCourseProgress($userId, $lessonId);
        
        return ['success' => true];
    }
    
    /**
     * Update course progress based on completed lessons
     */
    private static function updateCourseProgress($userId, $lessonId) {
        $pdo = getDBConnection();
        
        // Get course ID from lesson
        $stmt = $pdo->prepare("
            SELECT m.course_id FROM lessons l
            JOIN modules m ON l.module_id = m.id
            WHERE l.id = ?
        ");
        $stmt->execute([$lessonId]);
        $result = $stmt->fetch();
        
        if (!$result) return;
        
        $courseId = $result['course_id'];
        
        // Count total lessons in course
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total FROM lessons l
            JOIN modules m ON l.module_id = m.id
            WHERE m.course_id = ?
        ");
        $stmt->execute([$courseId]);
        $totalLessons = $stmt->fetch()['total'];
        
        // Count completed lessons
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as completed FROM lesson_progress lp
            JOIN lessons l ON lp.lesson_id = l.id
            JOIN modules m ON l.module_id = m.id
            WHERE m.course_id = ? AND lp.user_id = ? AND lp.completed = TRUE
        ");
        $stmt->execute([$courseId, $userId]);
        $completedLessons = $stmt->fetch()['completed'];
        
        // Calculate progress percentage
        $progress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
        
        // Update enrollment progress
        $stmt = $pdo->prepare("
            UPDATE enrollments 
            SET progress = ?, 
                last_accessed_at = NOW(),
                completed_at = CASE WHEN ? >= 100 THEN NOW() ELSE completed_at END,
                status = CASE WHEN ? >= 100 THEN 'completed' ELSE status END
            WHERE user_id = ? AND course_id = ?
        ");
        $stmt->execute([$progress, $progress, $progress, $userId, $courseId]);
    }
    
    /**
     * Get course reviews
     */
    public static function getReviews($courseId, $limit = 10) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            SELECT r.*, u.name as user_name, u.avatar_url
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.course_id = ? AND r.is_published = TRUE
            ORDER BY r.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$courseId, $limit]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Add course review
     */
    public static function addReview($userId, $courseId, $rating, $comment = null) {
        $pdo = getDBConnection();
        
        // Check if user is enrolled
        $stmt = $pdo->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
        $stmt->execute([$userId, $courseId]);
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'You must be enrolled to review this course'];
        }
        
        // Check if already reviewed
        $stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND course_id = ?");
        $stmt->execute([$userId, $courseId]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'You have already reviewed this course'];
        }
        
        // Add review
        $stmt = $pdo->prepare("
            INSERT INTO reviews (user_id, course_id, rating, comment, is_published) 
            VALUES (?, ?, ?, ?, TRUE)
        ");
        
        try {
            $stmt->execute([$userId, $courseId, $rating, $comment]);
            
            // Update course rating
            self::updateCourseRating($courseId);
            
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Review error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to add review'];
        }
    }
    
    /**
     * Update course rating
     */
    private static function updateCourseRating($courseId) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
            FROM reviews 
            WHERE course_id = ? AND is_published = TRUE
        ");
        $stmt->execute([$courseId]);
        $result = $stmt->fetch();
        
        $avgRating = round($result['avg_rating'] * 100); // Store as integer (e.g., 450 = 4.50)
        $reviewCount = $result['review_count'];
        
        $stmt = $pdo->prepare("UPDATE courses SET rating = ?, review_count = ? WHERE id = ?");
        $stmt->execute([$avgRating, $reviewCount, $courseId]);
    }
    
    /**
     * Search courses
     */
    public static function search($query, $category = null, $level = null, $limit = 12, $offset = 0) {
        $pdo = getDBConnection();
        
        $sql = "
            SELECT c.*, u.name as instructor_name, cat.name as category_name
            FROM courses c
            LEFT JOIN users u ON c.instructor_id = u.id
            LEFT JOIN categories cat ON c.category_id = cat.id
            WHERE c.is_published = TRUE
        ";
        
        $params = [];
        
        if ($query) {
            $sql .= " AND (c.title LIKE ? OR c.description LIKE ?)";
            $searchTerm = "%$query%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if ($category) {
            $sql .= " AND c.category_id = ?";
            $params[] = $category;
        }
        
        if ($level) {
            $sql .= " AND c.level = ?";
            $params[] = $level;
        }
        
        $sql .= " ORDER BY c.enrollment_count DESC, c.rating DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
}
