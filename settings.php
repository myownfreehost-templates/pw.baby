<?php
require 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? $user['username']);
    $email = sanitize($_POST['email'] ?? $user['email']);
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $birth_date = $_POST['birth_date'] ?? $user['birth_date'];
    $gender = sanitize($_POST['gender'] ?? $user['gender']);
    $photo_link = sanitize($_POST['photo_link'] ?? $user['photo_link']);

    // Check username availability
    $users = readJSON(USERS_FILE);
    foreach ($users as $u) {
        if ($u['username'] === $username && $u['id'] !== $user['id']) {
            $error = 'Username already taken.';
            break;
        }
        if ($u['email'] === $email && $u['id'] !== $user['id']) {
            $error = 'Email already registered.';
            break;
        }
    }

    if (!$error) {
        // Update password if provided
        if (!empty($password)) {
            if (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters.';
            } elseif ($password !== $password_confirm) {
                $error = 'Passwords do not match.';
            } else {
                $user['password'] = password_hash($password, PASSWORD_BCRYPT);
            }
        }

        if (!$error) {
            $user['username'] = $username;
            $user['email'] = $email;
            $user['birth_date'] = $birth_date;
            $user['gender'] = $gender;
            $user['photo_link'] = $photo_link;

            $users = array_map(function($u) use ($user) {
                return $u['id'] === $user['id'] ? $user : $u;
            }, $users);

            if (writeJSON(USERS_FILE, $users)) {
                $_SESSION['username'] = $user['username'];
                $success = 'Settings updated successfully!';
            } else {
                $error = 'Failed to update settings.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Social Network</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .settings-form { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { margin-bottom: 30px; color: #333; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; color: #555; font-weight: bold; }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="date"],
        select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        input:focus,
        select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 5px rgba(102, 126, 234, 0.3); }
        .error { background: #fee; color: #c33; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .success { background: #efe; color: #3c3; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        button { width: 100%; padding: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        button:hover { opacity: 0.9; }
        .back-link { display: inline-block; margin-top: 20px; color: #667eea; text-decoration: none; }
    </style>
</head>
<body>
    <header>
        <h1>Settings</h1>
    </header>
    <div class="container">
        <div class="settings-form">
            <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
            <?php if ($success): ?><div class="success"><?php echo $success; ?></div><?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="birth_date">Birth Date:</label>
                    <input type="date" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars($user['birth_date']); ?>">
                </div>
                <div class="form-group">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender">
                        <option value="unknown" <?php echo $user['gender'] === 'unknown' ? 'selected' : ''; ?>>Unknown</option>
                        <option value="male" <?php echo $user['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo $user['gender'] === 'female' ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="photo_link">Photo Link (URL):</label>
                    <input type="text" id="photo_link" name="photo_link" value="<?php echo htmlspecialchars($user['photo_link']); ?>">
                </div>
                <div class="form-group">
                    <label for="password">New Password (leave blank to keep current):</label>
                    <input type="password" id="password" name="password">
                </div>
                <div class="form-group">
                    <label for="password_confirm">Confirm New Password:</label>
                    <input type="password" id="password_confirm" name="password_confirm">
                </div>
                <button type="submit">Update Settings</button>
            </form>
            <a href="profile.php" class="back-link">← Back to Profile</a>
        </div>
    </div>
</body>
</html>