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
    $password_confirm = $_POST['password_confirm'] ?? '';
    $email = sanitize($_POST['email'] ?? '');
    $birth_date = $_POST['birth_date'] ?? '';
    $gender = sanitize($_POST['gender'] ?? 'unknown');
    $photo_link = sanitize($_POST['photo_link'] ?? '');

    if (empty($username) || empty($password) || empty($email)) {
        $error = 'Username, email, and password are required.';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $password_confirm) {
        $error = 'Passwords do not match.';
    } elseif (!validateEmail($email)) {
        $error = 'Invalid email address.';
    } else {
        $users = readJSON(USERS_FILE);

        // Check if username or email already exists
        foreach ($users as $u) {
            if ($u['username'] === $username) {
                $error = 'Username already exists.';
                break;
            }
            if ($u['email'] === $email) {
                $error = 'Email already exists.';
                break;
            }
        }

        if (!$error) {
            $new_user = [
                'id' => uniqid(),
                'username' => $username,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'email' => $email,
                'birth_date' => $birth_date,
                'gender' => $gender,
                'photo_link' => $photo_link,
                'posts_count' => 0,
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'registration_date' => date('Y-m-d H:i:s'),
                'last_visited' => date('Y-m-d H:i:s'),
                'online_status' => 'online',
                'status' => 'active'
            ];

            $users[] = $new_user;
            if (writeJSON(USERS_FILE, $users)) {
                $success = 'Registration successful! Please login.';
                // Clear form
                $_POST = [];
            } else {
                $error = 'Registration failed. Please try again.';
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
    <title>Register - Social Network</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .container { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); width: 100%; max-width: 500px; }
        h1 { text-align: center; margin-bottom: 30px; color: #333; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; color: #555; font-weight: bold; }
        input[type="text"],
        input[type="password"],
        input[type="email"],
        input[type="date"],
        select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        input:focus,
        select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 5px rgba(102, 126, 234, 0.3); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .error { background: #fee; color: #c33; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .success { background: #efe; color: #3c3; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        button { width: 100%; padding: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 5px; font-size: 16px; font-weight: bold; cursor: pointer; }
        button:hover { opacity: 0.9; }
        .links { text-align: center; margin-top: 20px; }
        .links a { color: #667eea; text-decoration: none; margin: 0 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Register</h1>
        <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="success"><?php echo $success; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="password_confirm">Confirm Password:</label>
                    <input type="password" id="password_confirm" name="password_confirm" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="birth_date">Birth Date:</label>
                    <input type="date" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars($_POST['birth_date'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender">
                        <option value="unknown">Unknown</option>
                        <option value="male" <?php echo ($_POST['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo ($_POST['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="photo_link">Photo Link (URL):</label>
                <input type="text" id="photo_link" name="photo_link" value="<?php echo htmlspecialchars($_POST['photo_link'] ?? ''); ?>" placeholder="https://example.com/photo.jpg">
            </div>
            <button type="submit">Register</button>
        </form>
        <div class="links">
            <a href="login.php">Login</a>
        </div>
    </div>
</body>
</html>