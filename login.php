<?php
require 'config.php';

if (isLoggedIn()) {
    header('Location: profile.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($username) || empty($password)) {
        $error = 'Username and password are required.';
    } else {
        $users = readJSON(USERS_FILE);
        $user = null;

        foreach ($users as $u) {
            if ($u['username'] === $username) {
                $user = $u;
                break;
            }
        }

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            if ($remember) {
                setcookie('username', $user['username'], time() + (86400 * 30), '/'); // 30 days
                setcookie('user_id', $user['id'], time() + (86400 * 30), '/');
            }

            // Update last visited and online status
            $user['last_visited'] = date('Y-m-d H:i:s');
            $user['online_status'] = 'online';
            $users = array_map(function($u) use ($user) {
                return $u['id'] === $user['id'] ? $user : $u;
            }, $users);
            writeJSON(USERS_FILE, $users);

            header('Location: profile.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

$remembered_username = $_COOKIE['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Social Network</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; justify-content: center; align-items: center; }
        .container { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); width: 100%; max-width: 400px; }
        h1 { text-align: center; margin-bottom: 30px; color: #333; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; color: #555; font-weight: bold; }
        input[type="text"],
        input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        input[type="text"]:focus,
        input[type="password"]:focus { outline: none; border-color: #667eea; box-shadow: 0 0 5px rgba(102, 126, 234, 0.3); }
        .checkbox-group { display: flex; align-items: center; }
        input[type="checkbox"] { margin-right: 8px; }
        .error { background: #fee; color: #c33; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .success { background: #efe; color: #3c3; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        button { width: 100%; padding: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 5px; font-size: 16px; font-weight: bold; cursor: pointer; }
        button:hover { opacity: 0.9; }
        .links { text-align: center; margin-top: 20px; }
        .links a { color: #667eea; text-decoration: none; margin: 0 10px; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="success"><?php echo $success; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($remembered_username); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group checkbox-group">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember" style="margin: 0; font-weight: normal;">Remember me</label>
            </div>
            <button type="submit">Login</button>
        </form>
        <div class="links">
            <a href="register.php">Register</a>
            <a href="admin.php">Admin Panel</a>
        </div>
    </div>
</body>
</html>