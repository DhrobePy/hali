<?php
require_once '../config/config.php';
requireLogin();
requireRole('admin');

$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_plan'])) {
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        $price = floatval($_POST['price']);
        $duration = intval($_POST['duration']);
        $features = sanitize($_POST['features']);
        
        $stmt = $pdo->prepare("
            INSERT INTO subscription_plans (name, description, price, duration_days, features, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$name, $description, $price, $duration, $features]);
        header('Location: /admin/subscriptions.php?message=plan_added');
        exit;
    } elseif (isset($_POST['delete_plan'])) {
        $id = intval($_POST['plan_id']);
        $stmt = $pdo->prepare("DELETE FROM subscription_plans WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: /admin/subscriptions.php?message=plan_deleted');
        exit;
    }
}

$stmt = $pdo->query("
    SELECT sp.*, 
    (SELECT COUNT(*) FROM subscriptions WHERE plan_id = sp.id) as active_subscriptions
    FROM subscription_plans sp 
    ORDER BY sp.price ASC
");
$plans = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Subscriptions - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-2xl);">
                <h1>Subscription Plans</h1>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 400px; gap: var(--spacing-2xl);">
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Plan Name</th>
                                <th>Price</th>
                                <th>Duration</th>
                                <th>Active Subs</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($plans as $plan): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($plan['name']); ?></td>
                                    <td><?php echo formatCurrency($plan['price']); ?></td>
                                    <td><?php echo $plan['duration_days']; ?> days</td>
                                    <td><?php echo $plan['active_subscriptions']; ?></td>
                                    <td>
                                        <form method="POST" onsubmit="return confirm('Delete this plan?');" style="display: inline;">
                                            <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <button type="submit" name="delete_plan" class="btn" style="padding: var(--spacing-sm); background: var(--error); color: white;">
                                                <i data-lucide="trash" style="width: 16px; height: 16px;"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="card" style="padding: var(--spacing-xl); height: fit-content;">
                    <h3 style="margin-bottom: var(--spacing-lg);">Add Plan</h3>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <div class="form-group">
                            <label class="form-label">Plan Name</label>
                            <input type="text" name="name" required class="form-input" placeholder="Pro Monthly">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Price (BDT)</label>
                            <input type="number" name="price" required class="form-input" placeholder="999" step="0.01">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Duration (Days)</label>
                            <input type="number" name="duration" required class="form-input" placeholder="30">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-input" rows="2"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Features</label>
                            <textarea name="features" class="form-input" rows="3" placeholder="Unlimited courses&#10;HD videos&#10;Certificate"></textarea>
                        </div>
                        <button type="submit" name="add_plan" class="btn btn-primary" style="width: 100%;">Add Plan</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
EOF1
<?php
require_once '../config/config.php';
requireLogin();
requireRole('admin');

$pdo = getDBConnection();
$stmt = $pdo->query("
    SELECT r.*, u.name as user_name, c.title as course_title
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    JOIN courses c ON r.course_id = c.id
    ORDER BY r.created_at DESC
");
$reviews = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Reviews - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <h1>Course Reviews</h1>
            
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>User</th>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $review): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($review['course_title']); ?></td>
                                <td><?php echo htmlspecialchars($review['user_name']); ?></td>
                                <td>
                                    <div style="color: var(--warning);">
                                        <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars(substr($review['comment'], 0, 50)); ?>...</td>
                                <td><?php echo date('M d, Y', strtotime($review['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
