<?php
require 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();

// Count messages
$messages = readJSON(MESSAGES_FILE);
$unread_messages = array_filter($messages, function($m) use ($user) {
    return $m['recipient_id'] === $user['id'] && !isset($m['read']);
});

// Count notifications
$notifications = readJSON(NOTIFICATIONS_FILE);
$unread_notifications = array_filter($notifications, function($n) use ($user) {
    return $n['user_id'] === $user['id'] && !isset($n['read']);
});

// Count posts
$posts = readJSON(POSTS_FILE);
$user_posts = array_filter($posts, function($p) use ($user) {
    return $p['author_id'] === $user['id'];
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo htmlspecialchars($user['username']); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .profile-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px; text-align: center; }
        .profile-photo { width: 150px; height: 150px; border-radius: 50%; background: #ddd; margin: 0 auto 20px; object-fit: cover; }
        .profile-info h2 { color: #333; margin-bottom: 10px; }
        .profile-info p { color: #666; margin-bottom: 5px; }
        .dashboard { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .dashboard-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        .dashboard-card h3 { color: #667eea; margin-bottom: 10px; font-size: 24px; }
        .dashboard-card p { color: #666; margin-bottom: 15px; }
        .count { font-size: 32px; font-weight: bold; color: #667eea; margin-bottom: 10px; }
        .badge { display: inline-block; background: #ffc107; color: #333; padding: 3px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; margin-left: 5px; }
        a { color: white; text-decoration: none; display: inline-block; padding: 10px 20px; background: #667eea; border-radius: 5px; margin: 5px; }
        a:hover { opacity: 0.9; }
        .btn-logout { background: #dc3545; }
        .btn-logout:hover { background: #c82333; }
    </style>
</head>
<body>
    <header>
        <h1>Profile - <?php echo htmlspecialchars($user['username']); ?></h1>
    </header>
    <div class="container">
        <div class="profile-card">
            <?php if (!empty($user['photo_link'])): ?>
                <img src="<?php echo htmlspecialchars($user['photo_link']); ?>" alt="Profile Photo" class="profile-photo">
            <?php else: ?>
                <div class="profile-photo"></div>
            <?php endif; ?>
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
                <p>Gender: <?php echo ucfirst($user['gender']); ?></p>
                <p>Member since: <?php echo date('M d, Y', strtotime($user['registration_date'])); ?></p>
                <p>Status: <span class="badge"><?php echo ucfirst($user['online_status']); ?></span></p>
            </div>
        </div>

        <div class="dashboard">
            <div class="dashboard-card">
                <h3>Posts</h3>
                <div class="count"><?php echo count($user_posts); ?></div>
                <a href="posts.php">View Feed</a>
            </div>
            <div class="dashboard-card">
                <h3>Messages</h3>
                <div class="count"><?php echo count($unread_messages); ?></div>
                <a href="messages.php">Go to Messages</a>
            </div>
            <div class="dashboard-card">
                <h3>Notifications</h3>
                <div class="count"><?php echo count($unread_notifications); ?></div>
                <a href="notifications.php">View Notifications</a>
            </div>
            <div class="dashboard-card">
                <h3>Users</h3>
                <div class="count"><?php echo count(readJSON(USERS_FILE)); ?></div>
                <a href="users.php">Browse Users</a>
            </div>
            <div class="dashboard-card">
                <h3>Account</h3>
                <p>Manage your settings</p>
                <a href="settings.php">Settings</a>
            </div>
        </div>

        <div style="text-align: center;">
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>
</body>
</html>