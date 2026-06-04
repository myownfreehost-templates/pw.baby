<?php
require 'config.php';

// Check admin authentication
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_password'])) {
    if (password_verify($_POST['admin_password'], ADMIN_PASSWORD)) {
        $_SESSION['is_admin'] = true;
    } else {
        $error = 'Invalid admin password.';
    }
}

if (!isAdmin()) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Panel - Access Denied</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; justify-content: center; align-items: center; }
            .container { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); width: 100%; max-width: 400px; }
            h1 { text-align: center; margin-bottom: 30px; color: #333; }
            .form-group { margin-bottom: 20px; }
            label { display: block; margin-bottom: 5px; color: #555; font-weight: bold; }
            input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
            input[type="password"]:focus { outline: none; border-color: #667eea; }
            .error { background: #fee; color: #c33; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
            button { width: 100%; padding: 12px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
            button:hover { opacity: 0.9; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Admin Panel</h1>
            <?php if (isset($error)): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="admin_password">Admin Password:</label>
                    <input type="password" id="admin_password" name="admin_password" required>
                </div>
                <button type="submit">Access Admin Panel</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Handle delete user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    $user_id = sanitize($_POST['user_id']);
    $users = readJSON(USERS_FILE);
    $users = array_filter($users, function($u) use ($user_id) {
        return $u['id'] !== $user_id;
    });
    writeJSON(USERS_FILE, array_values($users));
    echo json_encode(['status' => 'success', 'message' => 'User deleted.']);
    exit;
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    if ($_GET['action'] === 'get_users') {
        $page = (int)($_GET['page'] ?? 1);
        $filter = sanitize($_GET['filter'] ?? 'all');
        $search = sanitize($_GET['search'] ?? '');

        $users = readJSON(USERS_FILE);

        // Filter by status
        if ($filter === 'active') {
            $users = array_filter($users, function($u) { return $u['status'] === 'active'; });
        } elseif ($filter === 'inactive') {
            $users = array_filter($users, function($u) { return $u['status'] !== 'active'; });
        }

        // Search
        if (!empty($search)) {
            $users = array_filter($users, function($u) use ($search) {
                return strpos($u['username'], $search) !== false || strpos($u['email'], $search) !== false;
            });
        }

        $total = count($users);
        $offset = ($page - 1) * ITEMS_PER_PAGE;
        $users = array_slice(array_values($users), $offset, ITEMS_PER_PAGE);

        echo json_encode(['users' => $users, 'total' => $total, 'page' => $page]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - User Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; }
        header h1 { margin-bottom: 10px; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .controls { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: grid; grid-template-columns: auto auto 1fr auto; gap: 15px; align-items: center; }
        select, input[type="text"] { padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { opacity: 0.9; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        th { background: #667eea; color: white; padding: 15px; text-align: left; }
        td { padding: 12px 15px; border-bottom: 1px solid #ddd; }
        tr:hover { background: #f9f9f9; }
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .btn-delete { background: #dc3545; padding: 6px 12px; font-size: 12px; }
        .btn-delete:hover { background: #c82333; }
        .pagination { margin-top: 20px; text-align: center; }
        .pagination button { margin: 0 5px; }
        .logout-btn { background: #dc3545; margin-top: 20px; width: 100%; }
    </style>
</head>
<body>
    <header>
        <h1>Admin Panel</h1>
        <p>User Management System</p>
    </header>
    <div class="container">
        <div class="controls">
            <select id="filterStatus">
                <option value="all">All Users</option>
                <option value="active">Active Users</option>
                <option value="inactive">Inactive Users</option>
            </select>
            <input type="text" id="searchUsers" placeholder="Search username or email...">
            <button onclick="searchUsers()">Search</button>
            <button class="logout-btn" onclick="logout()">Logout</button>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Gender</th>
                    <th>Registration Date</th>
                    <th>Status</th>
                    <th>Online</th>
                    <th>Posts</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="usersTable"></tbody>
        </table>
        <div class="pagination">
            <button onclick="prevPage()">Previous</button>
            <span id="pageInfo">Page 1</span>
            <button onclick="nextPage()">Next</button>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let currentFilter = 'all';
        let currentSearch = '';

        function loadUsers() {
            fetch(`admin.php?action=get_users&page=${currentPage}&filter=${currentFilter}&search=${currentSearch}`)
                .then(r => r.json())
                .then(data => {
                    let html = '';
                    data.users.forEach(user => {
                        html += `
                            <tr>
                                <td>${user.username}</td>
                                <td>${user.email}</td>
                                <td>${user.gender}</td>
                                <td>${user.registration_date}</td>
                                <td><span class="status-badge status-${user.status}">${user.status}</span></td>
                                <td>${user.online_status}</td>
                                <td>${user.posts_count}</td>
                                <td><button class="btn-delete" onclick="deleteUser('${user.id}')">Delete</button></td>
                            </tr>
                        `;
                    });
                    document.getElementById('usersTable').innerHTML = html;
                    document.getElementById('pageInfo').innerText = `Page ${data.page} of ${Math.ceil(data.total / 10)}`;
                });
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                fetch('admin.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=delete_user&user_id=${userId}`
                }).then(() => loadUsers());
            }
        }

        function searchUsers() {
            currentSearch = document.getElementById('searchUsers').value;
            currentPage = 1;
            loadUsers();
        }

        document.getElementById('filterStatus').addEventListener('change', (e) => {
            currentFilter = e.target.value;
            currentPage = 1;
            loadUsers();
        });

        function nextPage() { currentPage++; loadUsers(); }
        function prevPage() { if (currentPage > 1) currentPage--; loadUsers(); }
        function logout() { window.location.href = 'logout.php'; }

        loadUsers();
    </script>
</body>
</html>