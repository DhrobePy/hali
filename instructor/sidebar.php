<aside class="sidebar">
    <div style="margin-bottom: var(--spacing-2xl);">
        <a href="/" style="display: flex; align-items: center; gap: var(--spacing-sm); margin-bottom: var(--spacing-lg);">
            <i data-lucide="book-open" style="width: 24px; height: 24px; color: var(--primary);"></i>
            <span style="font-weight: 700;" class="text-gradient"><?php echo SITE_NAME; ?></span>
        </a>
        <div>
            <div style="font-weight: 600; margin-bottom: var(--spacing-xs);"><?php echo htmlspecialchars($user['name']); ?></div>
            <div style="font-size: 0.875rem; color: var(--muted-foreground);">Instructor</div>
        </div>
    </div>
    
    <nav>
        <ul class="sidebar-nav">
            <li><a href="/instructor/dashboard.php"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
            <li><a href="/instructor/courses.php"><i data-lucide="book-open"></i> My Courses</a></li>
            <li><a href="/instructor/create-course.php"><i data-lucide="plus"></i> Create Course</a></li>
            <li><a href="/instructor/students.php"><i data-lucide="users"></i> Students</a></li>
            <li><a href="/logout.php"><i data-lucide="log-out"></i> Logout</a></li>
        </ul>
    </nav>
</aside>
