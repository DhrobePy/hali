<?php
require_once 'config/config.php';
require_once 'includes/Course.php';

// Get featured courses
$featuredCourses = Course::getFeatured(6);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Master New Skills with Expert-Led Courses</title>
    <meta name="description" content="Access premium courses, learn at your own pace, and achieve your goals with our comprehensive learning management system.">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/images/favicon.png">
    
    <!-- CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <!-- Glassmorphic Navbar -->
    <nav class="navbar">
        <div class="container navbar-container">
            <a href="/" class="navbar-brand">
                <i data-lucide="book-open" style="width: 32px; height: 32px;"></i>
                <span class="text-gradient"><?php echo SITE_NAME; ?></span>
            </a>
            
            <ul class="navbar-menu">
                <li><a href="#features" class="navbar-link">Features</a></li>
                <li><a href="#courses" class="navbar-link">Courses</a></li>
                <li><a href="#pricing" class="navbar-link">Pricing</a></li>
                <li><a href="#faq" class="navbar-link">FAQ</a></li>
            </ul>
            
            <div style="display: flex; align-items: center; gap: 1rem;">
                <?php if (isLoggedIn()): ?>
                    <a href="/student/dashboard.php" class="btn btn-primary">Dashboard</a>
                <?php else: ?>
                    <a href="/login.php" class="btn btn-secondary">Sign In</a>
                    <a href="/register.php" class="btn btn-primary">Get Started</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section with Parallax -->
    <section class="hero">
        <!-- Parallax Background Layers -->
        <div class="parallax-bg">
            <div class="parallax-layer" data-speed="-0.2" style="top: 80px; left: 40px; width: 288px; height: 288px; background: rgba(102, 126, 234, 0.1);"></div>
            <div class="parallax-layer" data-speed="-0.3" style="top: 160px; right: 80px; width: 384px; height: 384px; background: rgba(102, 126, 234, 0.05);"></div>
            <div class="parallax-layer" data-speed="-0.1" style="bottom: 80px; left: 33%; width: 256px; height: 256px; background: rgba(102, 126, 234, 0.1);"></div>
        </div>
        
        <div class="container hero-content">
            <div class="hero-badge">🚀 Modern Learning Platform</div>
            
            <h1 class="hero-title">
                Master New Skills with<br>
                <span class="text-gradient">Expert-Led Courses</span>
            </h1>
            
            <p class="hero-description">
                Access premium courses, learn at your own pace, and achieve your goals with our comprehensive learning management system.
            </p>
            
            <div class="hero-actions">
                <a href="/register.php" class="btn btn-primary btn-lg">
                    Start Learning Today
                    <i data-lucide="arrow-right" style="width: 20px; height: 20px; margin-left: 8px;"></i>
                </a>
                <a href="#courses" class="btn btn-outline btn-lg">
                    <i data-lucide="play" style="width: 20px; height: 20px; margin-right: 8px;"></i>
                    Browse Courses
                </a>
            </div>
            
            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-item">
                    <i data-lucide="users" class="stat-icon"></i>
                    <div class="stat-value text-gradient">10,000+</div>
                    <div class="stat-label">Active Students</div>
                </div>
                <div class="stat-item">
                    <i data-lucide="video" class="stat-icon"></i>
                    <div class="stat-value text-gradient">500+</div>
                    <div class="stat-label">Video Lessons</div>
                </div>
                <div class="stat-item">
                    <i data-lucide="award" class="stat-icon"></i>
                    <div class="stat-value text-gradient">5,000+</div>
                    <div class="stat-label">Certificates</div>
                </div>
                <div class="stat-item">
                    <i data-lucide="star" class="stat-icon"></i>
                    <div class="stat-value text-gradient">4.9</div>
                    <div class="stat-label">Average Rating</div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section id="features" class="section" style="background: var(--muted);">
        <div class="container">
            <div class="section-header">
                <h2>Why Choose <?php echo SITE_NAME; ?>?</h2>
                <p class="section-description">
                    Experience a modern learning platform built with cutting-edge technology and designed for your success.
                </p>
            </div>
            
            <div class="features-grid">
                <div class="card card-glass feature-card">
                    <div class="feature-icon">
                        <i data-lucide="video"></i>
                    </div>
                    <h3 class="feature-title">HD Video Lessons</h3>
                    <p class="feature-description">
                        Crystal-clear video content with secure streaming and domain-level protection.
                    </p>
                </div>
                
                <div class="card card-glass feature-card">
                    <div class="feature-icon">
                        <i data-lucide="shield"></i>
                    </div>
                    <h3 class="feature-title">Secure Content</h3>
                    <p class="feature-description">
                        Advanced security measures to protect course materials from unauthorized access.
                    </p>
                </div>
                
                <div class="card card-glass feature-card">
                    <div class="feature-icon">
                        <i data-lucide="bar-chart"></i>
                    </div>
                    <h3 class="feature-title">Progress Tracking</h3>
                    <p class="feature-description">
                        Monitor your learning journey with detailed analytics and completion tracking.
                    </p>
                </div>
                
                <div class="card card-glass feature-card">
                    <div class="feature-icon">
                        <i data-lucide="check-circle"></i>
                    </div>
                    <h3 class="feature-title">Interactive Quizzes</h3>
                    <p class="feature-description">
                        Test your knowledge with engaging quizzes and receive instant feedback.
                    </p>
                </div>
                
                <div class="card card-glass feature-card">
                    <div class="feature-icon">
                        <i data-lucide="award"></i>
                    </div>
                    <h3 class="feature-title">Certificates</h3>
                    <p class="feature-description">
                        Earn recognized certificates upon course completion to showcase your skills.
                    </p>
                </div>
                
                <div class="card card-glass feature-card">
                    <div class="feature-icon">
                        <i data-lucide="zap"></i>
                    </div>
                    <h3 class="feature-title">Fast & Responsive</h3>
                    <p class="feature-description">
                        Lightning-fast platform optimized for seamless learning on any device.
                    </p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Featured Courses -->
    <section id="courses" class="section">
        <div class="container">
            <div class="section-header">
                <h2>Featured Courses</h2>
                <p class="section-description">
                    Explore our most popular courses taught by industry experts.
                </p>
            </div>
            
            <div class="course-grid">
                <?php if (count($featuredCourses) > 0): ?>
                    <?php foreach ($featuredCourses as $course): ?>
                        <div class="course-card">
                            <?php if ($course['thumbnail_url']): ?>
                                <img src="<?php echo htmlspecialchars($course['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="course-thumbnail">
                            <?php else: ?>
                                <div class="course-thumbnail" style="display: flex; align-items: center; justify-content: center;">
                                    <i data-lucide="book-open" style="width: 64px; height: 64px; color: rgba(255,255,255,0.5);"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="course-content">
                                <div class="course-meta">
                                    <span class="course-badge"><?php echo ucfirst($course['level']); ?></span>
                                    <div class="course-rating">
                                        <i data-lucide="star" style="width: 16px; height: 16px; fill: var(--primary); color: var(--primary);"></i>
                                        <?php echo number_format($course['rating'] / 100, 1); ?>
                                    </div>
                                </div>
                                
                                <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                                <p class="course-description"><?php echo htmlspecialchars($course['description'] ?? ''); ?></p>
                                
                                <div class="course-footer">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <i data-lucide="users" style="width: 16px; height: 16px;"></i>
                                        <?php echo number_format($course['enrollment_count']); ?> students
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <i data-lucide="clock" style="width: 16px; height: 16px;"></i>
                                        <?php echo $course['language']; ?>
                                    </div>
                                </div>
                                
                                <a href="/course.php?slug=<?php echo urlencode($course['slug']); ?>" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">View Course</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Placeholder courses -->
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <div class="course-card">
                            <div class="course-thumbnail" style="display: flex; align-items: center; justify-content: center;">
                                <i data-lucide="book-open" style="width: 64px; height: 64px; color: rgba(255,255,255,0.5);"></i>
                            </div>
                            <div class="course-content">
                                <span class="course-badge">Coming Soon</span>
                                <h3 class="course-title">Course <?php echo $i; ?></h3>
                                <p class="course-description">Exciting new course coming soon!</p>
                                <button class="btn btn-primary" style="width: 100%; margin-top: 1rem;" disabled>Coming Soon</button>
                            </div>
                        </div>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
    <!-- Pricing Section -->
    <section id="pricing" class="section" style="background: var(--muted);">
        <div class="container">
            <div class="section-header">
                <h2>Simple, Transparent Pricing</h2>
                <p class="section-description">
                    Choose the plan that fits your learning goals. All plans include full access to course materials.
                </p>
            </div>
            
            <div class="pricing-grid">
                <div class="card card-glass pricing-card">
                    <h3 class="pricing-name">Monthly</h3>
                    <div class="pricing-price text-gradient">
                        ৳999
                        <span class="pricing-period">/month</span>
                    </div>
                    <ul class="pricing-features">
                        <li><i data-lucide="check-circle" style="width: 20px; height: 20px; color: var(--primary);"></i> Access to all courses</li>
                        <li><i data-lucide="check-circle" style="width: 20px; height: 20px; color: var(--primary);"></i> HD video streaming</li>
                        <li><i data-lucide="check-circle" style="width: 20px; height: 20px; color: var(--primary);"></i> Progress tracking</li>
                        <li><i data-lucide="check-circle" style="width: 20px; height: 20px; color: var(--primary);"></i> Quizzes & assessments</li>
                        <li><i data-lucide="check-circle" style="width: 20px; height: 20px; color: var(--primary);"></i> Email support</li>
                    </ul>
                    <a href="/register.php" class="btn btn-outline" style="width: 100%;">Get Started</a>
                </div>
                
                <div class="card card-glass pricing-card popular">
                    <div class="pricing-badge">Most Popular</div>
                    <h3 class="pricing-name">Annual</h3>
                    <div class="pricing-price text-gradient">
                        ৳9,999
                        <span class="pricing-period">/year</span>
                    </div>
                    <ul class="pricing-features">
                        <li><i data-lucide="check-circle" style="width: 20px; height: 20px; color: var(--primary);"></i> Everything in Monthly</li>
                        <li><i data-lucide="check-circle" style="width: 20px; height: 20px; color: var(--primary);"></i> Save 17% annually</li>
                        <li><i data-lucide="check-circle" style="width: 20px; height: 20px; color: var(--primary);"></i> Priority support</li>
                        <li><i data-lucide="check-circle" style="width: 20px; height: 20px; color: var(--primary);"></i> Offline downloads</li>
                        <li><i data-lucide="check-circle" style="width: 20px; height: 20px; color: var(--primary);"></i> Certificates included</li>
                    </ul>
                    <a href="/register.php" class="btn btn-primary" style="width: 100%;">Get Started</a>
                </div>
                
                <div class="card card-glass pricing-card">
                    <h3 class="pricing-name">Per Course</h3>
                    <div class="pricing-price text-gradient">
                        ৳499
                        <span class="pricing-period">/course</span>
                    </div>
                    <ul class="pricing-features">
                        <li><i data-lucide="check-circle" style="width: 20px; height: 20px; color: var(--primary);"></i> Single course access</li>
                        <li><i data-lucide="check-circle" style="width: 20px; height: 20px; color: var(--primary);"></i> Lifetime access</li>
                        <li><i data-lucide="check-circle" style="width: 20px; height: 20px; color: var(--primary);"></i> HD video streaming</li>
                        <li><i data-lucide="check-circle" style="width: 20px; height: 20px; color: var(--primary);"></i> Course certificate</li>
                        <li><i data-lucide="check-circle" style="width: 20px; height: 20px; color: var(--primary);"></i> Community access</li>
                    </ul>
                    <a href="/register.php" class="btn btn-outline" style="width: 100%;">Get Started</a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- FAQ Section -->
    <section id="faq" class="section">
        <div class="container">
            <div class="section-header">
                <h2>Frequently Asked Questions</h2>
                <p class="section-description">Got questions? We've got answers.</p>
            </div>
            
            <div style="max-width: 800px; margin: 0 auto;">
                <div class="card card-glass" style="margin-bottom: 1rem;">
                    <h4>How do I access the courses?</h4>
                    <p class="text-muted">After subscribing, you'll get instant access to all courses. Simply log in to your dashboard and start learning.</p>
                </div>
                
                <div class="card card-glass" style="margin-bottom: 1rem;">
                    <h4>Can I cancel my subscription anytime?</h4>
                    <p class="text-muted">Yes, you can cancel your subscription at any time. You'll continue to have access until the end of your billing period.</p>
                </div>
                
                <div class="card card-glass" style="margin-bottom: 1rem;">
                    <h4>Do I get a certificate after completing a course?</h4>
                    <p class="text-muted">Yes! You'll receive a certificate of completion for every course you finish, which you can share on LinkedIn or your resume.</p>
                </div>
                
                <div class="card card-glass" style="margin-bottom: 1rem;">
                    <h4>What payment methods do you accept?</h4>
                    <p class="text-muted">We accept bKash and Nagad for convenient local payments in Bangladesh.</p>
                </div>
                
                <div class="card card-glass">
                    <h4>Is there a free trial?</h4>
                    <p class="text-muted">We offer free preview lessons for most courses so you can try before you subscribe.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="section bg-gradient-primary" style="color: white;">
        <div class="container text-center">
            <h2 style="color: white; margin-bottom: 1.5rem;">Ready to Start Learning?</h2>
            <p style="font-size: 1.25rem; margin-bottom: 2rem; max-width: 700px; margin-left: auto; margin-right: auto; color: rgba(255,255,255,0.9);">
                Join thousands of students already learning on <?php echo SITE_NAME; ?>. Start your journey today!
            </p>
            <a href="/register.php" class="btn btn-secondary btn-lg">
                Get Started Now
                <i data-lucide="arrow-right" style="width: 20px; height: 20px; margin-left: 8px;"></i>
            </a>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <div class="footer-brand">
                        <i data-lucide="book-open" style="width: 24px; height: 24px;"></i>
                        <span class="text-gradient"><?php echo SITE_NAME; ?></span>
                    </div>
                    <p class="footer-description">
                        Empowering learners worldwide with quality education.
                    </p>
                </div>
                
                <div>
                    <h6 class="footer-title">Platform</h6>
                    <ul class="footer-links">
                        <li><a href="#courses">Courses</a></li>
                        <li><a href="#pricing">Pricing</a></li>
                        <li><a href="#features">Features</a></li>
                    </ul>
                </div>
                
                <div>
                    <h6 class="footer-title">Support</h6>
                    <ul class="footer-links">
                        <li><a href="#faq">FAQ</a></li>
                        <li><a href="/contact.php">Contact</a></li>
                        <li><a href="/help.php">Help Center</a></li>
                    </ul>
                </div>
                
                <div>
                    <h6 class="footer-title">Legal</h6>
                    <ul class="footer-links">
                        <li><a href="/privacy.php">Privacy Policy</a></li>
                        <li><a href="/terms.php">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script src="/assets/js/parallax.js"></script>
    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
</body>
</html>
