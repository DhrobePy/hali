-- Fajracct Secured LMS Database Schema
-- MySQL 5.7+ / MariaDB 10.2+

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `open_id` VARCHAR(64) NULL UNIQUE,
  `email` VARCHAR(320) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NULL,
  `name` VARCHAR(255) NOT NULL,
  `phone_number` VARCHAR(20) NULL,
  `role` ENUM('admin', 'instructor', 'student') DEFAULT 'student' NOT NULL,
  `email_verified` BOOLEAN DEFAULT FALSE NOT NULL,
  `phone_verified` BOOLEAN DEFAULT FALSE NOT NULL,
  `bio` TEXT NULL,
  `avatar_url` TEXT NULL,
  `login_method` VARCHAR(64) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
  `last_signed_in` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- OTP tokens table
CREATE TABLE IF NOT EXISTS `otp_tokens` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `token` VARCHAR(10) NOT NULL,
  `type` ENUM('email', 'phone', 'password_reset') NOT NULL,
  `expires_at` TIMESTAMP NOT NULL,
  `verified` BOOLEAN DEFAULT FALSE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_type` (`user_id`, `type`),
  INDEX `idx_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Subscription plans table
CREATE TABLE IF NOT EXISTS `subscription_plans` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
  `type` ENUM('monthly', 'annual', 'per_course', 'lifetime') NOT NULL,
  `price` INT UNSIGNED NOT NULL COMMENT 'Price in paisa (smallest currency unit)',
  `currency` VARCHAR(10) DEFAULT 'BDT' NOT NULL,
  `duration_days` INT UNSIGNED NULL COMMENT 'NULL for lifetime plans',
  `features` TEXT NULL COMMENT 'JSON string of features',
  `is_active` BOOLEAN DEFAULT TRUE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
  INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User subscriptions table
CREATE TABLE IF NOT EXISTS `subscriptions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `plan_id` INT UNSIGNED NOT NULL,
  `course_id` INT UNSIGNED NULL COMMENT 'For per-course subscriptions',
  `status` ENUM('active', 'expired', 'cancelled', 'pending') NOT NULL,
  `start_date` TIMESTAMP NOT NULL,
  `end_date` TIMESTAMP NULL COMMENT 'NULL for lifetime subscriptions',
  `auto_renew` BOOLEAN DEFAULT FALSE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans`(`id`),
  INDEX `idx_user_status` (`user_id`, `status`),
  INDEX `idx_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments table
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `subscription_id` INT UNSIGNED NULL,
  `amount` INT UNSIGNED NOT NULL,
  `currency` VARCHAR(10) DEFAULT 'BDT' NOT NULL,
  `gateway` ENUM('bkash', 'nagad', 'manual') NOT NULL,
  `transaction_id` VARCHAR(255) NULL,
  `status` ENUM('pending', 'completed', 'failed', 'refunded') NOT NULL,
  `metadata` TEXT NULL COMMENT 'JSON string for gateway-specific data',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions`(`id`) ON DELETE SET NULL,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_transaction` (`transaction_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `parent_id` INT UNSIGNED NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
  FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  INDEX `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Courses table
CREATE TABLE IF NOT EXISTS `courses` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `instructor_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NULL,
  `thumbnail_url` TEXT NULL,
  `trailer_video_url` TEXT NULL,
  `level` ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner' NOT NULL,
  `language` VARCHAR(50) DEFAULT 'en' NOT NULL,
  `price` INT UNSIGNED NULL COMMENT 'For per-course pricing',
  `is_published` BOOLEAN DEFAULT FALSE NOT NULL,
  `is_featured` BOOLEAN DEFAULT FALSE NOT NULL,
  `enrollment_count` INT UNSIGNED DEFAULT 0 NOT NULL,
  `rating` INT UNSIGNED DEFAULT 0 NOT NULL COMMENT 'Average rating * 100 (e.g., 450 = 4.50)',
  `review_count` INT UNSIGNED DEFAULT 0 NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
  FOREIGN KEY (`instructor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  INDEX `idx_slug` (`slug`),
  INDEX `idx_instructor` (`instructor_id`),
  INDEX `idx_published` (`is_published`),
  INDEX `idx_featured` (`is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Course modules table
CREATE TABLE IF NOT EXISTS `modules` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `course_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `order_index` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
  INDEX `idx_course_order` (`course_id`, `order_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lessons table
CREATE TABLE IF NOT EXISTS `lessons` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `module_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `video_url` TEXT NOT NULL,
  `video_provider` ENUM('vimeo', 'bunny', 'youtube') DEFAULT 'vimeo' NOT NULL,
  `video_duration` INT UNSIGNED NULL COMMENT 'Duration in seconds',
  `order_index` INT UNSIGNED NOT NULL,
  `is_free` BOOLEAN DEFAULT FALSE NOT NULL COMMENT 'Preview lessons',
  `resources` TEXT NULL COMMENT 'JSON string of downloadable resources',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
  FOREIGN KEY (`module_id`) REFERENCES `modules`(`id`) ON DELETE CASCADE,
  INDEX `idx_module_order` (`module_id`, `order_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Course enrollments table
CREATE TABLE IF NOT EXISTS `enrollments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `course_id` INT UNSIGNED NOT NULL,
  `subscription_id` INT UNSIGNED NULL,
  `status` ENUM('active', 'completed', 'dropped') DEFAULT 'active' NOT NULL,
  `progress` INT UNSIGNED DEFAULT 0 NOT NULL COMMENT 'Percentage 0-100',
  `enrolled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  `completed_at` TIMESTAMP NULL,
  `last_accessed_at` TIMESTAMP NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions`(`id`) ON DELETE SET NULL,
  UNIQUE KEY `unique_enrollment` (`user_id`, `course_id`),
  INDEX `idx_user` (`user_id`),
  INDEX `idx_course` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lesson progress table
CREATE TABLE IF NOT EXISTS `lesson_progress` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `lesson_id` INT UNSIGNED NOT NULL,
  `completed` BOOLEAN DEFAULT FALSE NOT NULL,
  `watched_duration` INT UNSIGNED DEFAULT 0 NOT NULL COMMENT 'Seconds watched',
  `last_position` INT UNSIGNED DEFAULT 0 NOT NULL COMMENT 'Last playback position in seconds',
  `completed_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`lesson_id`) REFERENCES `lessons`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_progress` (`user_id`, `lesson_id`),
  INDEX `idx_user` (`user_id`),
  INDEX `idx_lesson` (`lesson_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quizzes table
CREATE TABLE IF NOT EXISTS `quizzes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `course_id` INT UNSIGNED NOT NULL,
  `module_id` INT UNSIGNED NULL,
  `lesson_id` INT UNSIGNED NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `type` ENUM('quiz', 'exam') DEFAULT 'quiz' NOT NULL,
  `passing_score` INT UNSIGNED DEFAULT 70 NOT NULL COMMENT 'Percentage',
  `time_limit` INT UNSIGNED NULL COMMENT 'Time limit in minutes (NULL for untimed)',
  `max_attempts` INT UNSIGNED NULL COMMENT 'NULL for unlimited',
  `is_published` BOOLEAN DEFAULT FALSE NOT NULL,
  `order_index` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`module_id`) REFERENCES `modules`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`lesson_id`) REFERENCES `lessons`(`id`) ON DELETE CASCADE,
  INDEX `idx_course` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quiz questions table
CREATE TABLE IF NOT EXISTS `questions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `quiz_id` INT UNSIGNED NOT NULL,
  `question_text` TEXT NOT NULL,
  `type` ENUM('multiple_choice', 'true_false') NOT NULL,
  `options` TEXT NOT NULL COMMENT 'JSON array of options',
  `correct_answer` TEXT NOT NULL COMMENT 'JSON (index or boolean)',
  `explanation` TEXT NULL,
  `points` INT UNSIGNED DEFAULT 1 NOT NULL,
  `order_index` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
  FOREIGN KEY (`quiz_id`) REFERENCES `quizzes`(`id`) ON DELETE CASCADE,
  INDEX `idx_quiz_order` (`quiz_id`, `order_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quiz attempts table
CREATE TABLE IF NOT EXISTS `quiz_attempts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `quiz_id` INT UNSIGNED NOT NULL,
  `score` INT UNSIGNED NOT NULL COMMENT 'Percentage',
  `total_points` INT UNSIGNED NOT NULL,
  `earned_points` INT UNSIGNED NOT NULL,
  `answers` TEXT NOT NULL COMMENT 'JSON object of question_id: answer',
  `passed` BOOLEAN NOT NULL,
  `time_spent` INT UNSIGNED NULL COMMENT 'Time spent in seconds',
  `started_at` TIMESTAMP NOT NULL,
  `completed_at` TIMESTAMP NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`quiz_id`) REFERENCES `quizzes`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_quiz` (`user_id`, `quiz_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Course reviews table
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `course_id` INT UNSIGNED NOT NULL,
  `rating` INT UNSIGNED NOT NULL COMMENT '1-5',
  `comment` TEXT NULL,
  `is_published` BOOLEAN DEFAULT TRUE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_review` (`user_id`, `course_id`),
  INDEX `idx_course` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Certificates table
CREATE TABLE IF NOT EXISTS `certificates` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `course_id` INT UNSIGNED NOT NULL,
  `certificate_number` VARCHAR(100) NOT NULL UNIQUE,
  `issued_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  `certificate_url` TEXT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_certificate` (`user_id`, `course_id`),
  INDEX `idx_certificate_number` (`certificate_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications table
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('info', 'success', 'warning', 'error') DEFAULT 'info' NOT NULL,
  `is_read` BOOLEAN DEFAULT FALSE NOT NULL,
  `action_url` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_read` (`user_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
INSERT INTO `users` (`email`, `password_hash`, `name`, `role`, `email_verified`) 
VALUES ('admin@fajracct.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYIxIvJ7aZW', 'Admin User', 'admin', TRUE)
ON DUPLICATE KEY UPDATE `email` = `email`;

-- Insert sample subscription plans
INSERT INTO `subscription_plans` (`name`, `description`, `type`, `price`, `duration_days`, `features`) VALUES
('Monthly Access', 'Full access to all courses for one month', 'monthly', 99900, 30, '["Access to all courses", "HD video streaming", "Progress tracking", "Quizzes & assessments", "Email support"]'),
('Annual Access', 'Full access to all courses for one year - Save 17%', 'annual', 999900, 365, '["Everything in Monthly", "Save 17% annually", "Priority support", "Offline downloads", "Certificates included"]'),
('Per Course', 'Lifetime access to a single course', 'per_course', 49900, NULL, '["Single course access", "Lifetime access", "HD video streaming", "Course certificate", "Community access"]')
ON DUPLICATE KEY UPDATE `name` = `name`;
