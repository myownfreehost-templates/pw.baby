<?php
// Configuration File
$DB_PATH = __DIR__;
define('USERS_FILE', $DB_PATH . '/users.json');
define('MESSAGES_FILE', $DB_PATH . '/messages.json');
define('POSTS_FILE', $DB_PATH . '/posts.json');
define('FRIENDS_FILE', $DB_PATH . '/friends.json');
define('NOTIFICATIONS_FILE', $DB_PATH . '/notifications.json');
define('ADMIN_PASSWORD', password_hash('12345678', PASSWORD_BCRYPT));
define('ITEMS_PER_PAGE', 10);

session_start();

// Utility Functions
function readJSON($file) {
    if (!file_exists($file)) {
        return [];
    }
    $data = file_get_contents($file);
    return json_decode($data, true) ?? [];
}

function writeJSON($file, $data) {
    return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    $users = readJSON(USERS_FILE);
    foreach ($users as $user) {
        if ($user['id'] === $_SESSION['user_id']) {
            return $user;
        }
    }
    return null;
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}
?>