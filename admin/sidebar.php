<aside class="sidebar">
    <div style="margin-bottom: var(--spacing-2xl);">
        <a href="/" style="display: flex; align-items: center; gap: var(--spacing-sm); margin-bottom: var(--spacing-lg);">
            <i data-lucide="book-open" style="width: 24px; height: 24px; color: var(--primary);"></i>
            <span style="font-weight: 700;" class="text-gradient"><?php echo SITE_NAME; ?></span>
        </a>
        <div>
            <div style="font-weight: 600; margin-bottom: var(--spacing-xs);"><?php echo htmlspecialchars($user['name'] ?? getCurrentUser()['name']); ?></div>
            <div style="font-size: 0.875rem; color: var(--muted-foreground);">Administrator</div>
        </div>
    </div>
    
    <nav>
        <ul class="sidebar-nav">
            <li><a href="/admin/dashboard.php"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
            <li><a href="/admin/users.php"><i data-lucide="users"></i> Users</a></li>
            <li><a href="/admin/courses.php"><i data-lucide="book-open"></i> Courses</a></li>
            <li><a href="/admin/categories.php"><i data-lucide="folder"></i> Categories</a></li>
            <li><a href="/admin/subscriptions.php"><i data-lucide="credit-card"></i> Subscriptions</a></li>
            <li><a href="/admin/payments.php"><i data-lucide="dollar-sign"></i> Payments</a></li>
            <li><a href="/admin/reviews.php"><i data-lucide="star"></i> Reviews</a></li>
            <li><a href="/admin/settings.php"><i data-lucide="settings"></i> Settings</a></li>
            <li><a href="/logout.php"><i data-lucide="log-out"></i> Logout</a></li>
        </ul>
    </nav>
</aside>
