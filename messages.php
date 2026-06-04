<?php
require 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$tab = $_GET['tab'] ?? 'inbox';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $messages = readJSON(MESSAGES_FILE);

    if ($action === 'send_message') {
        $recipient_username = sanitize($_POST['recipient_username']);
        $content = sanitize($_POST['content']);
        $users = readJSON(USERS_FILE);
        $recipient = null;

        foreach ($users as $u) {
            if ($u['username'] === $recipient_username) {
                $recipient = $u;
                break;
            }
        }

        if ($recipient && !empty($content)) {
            $new_message = [
                'id' => uniqid(),
                'sender_id' => $user['id'],
                'recipient_id' => $recipient['id'],
                'content' => $content,
                'date' => date('Y-m-d H:i:s'),
                'folder' => 'sent'
            ];
            $messages[] = $new_message;
            writeJSON(MESSAGES_FILE, $messages);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid recipient or empty message']);
        }
        exit;
    } elseif ($action === 'delete_message') {
        $message_id = sanitize($_POST['message_id']);
        $messages = array_filter($messages, function($m) use ($message_id) {
            return $m['id'] !== $message_id;
        });
        writeJSON(MESSAGES_FILE, array_values($messages));
        echo json_encode(['status' => 'success']);
        exit;
    } elseif ($action === 'delete_all') {
        $folder = sanitize($_POST['folder']);
        $messages = array_filter($messages, function($m) use ($folder, $user) {
            if ($folder === 'trash') return $m['folder'] !== 'trash';
            if ($folder === 'sent') return !($m['folder'] === 'sent' && $m['sender_id'] === $user['id']);
            return !($m['folder'] === 'inbox' && $m['recipient_id'] === $user['id']);
        });
        writeJSON(MESSAGES_FILE, array_values($messages));
        echo json_encode(['status' => 'success']);
        exit;
    }
}

// Get messages for current tab
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_messages') {
    $page = (int)($_GET['page'] ?? 1);
    $tab = sanitize($_GET['tab'] ?? 'inbox');
    $messages = readJSON(MESSAGES_FILE);
    $users = readJSON(USERS_FILE);

    if ($tab === 'inbox') {
        $messages = array_filter($messages, function($m) use ($user) {
            return $m['recipient_id'] === $user['id'] && $m['folder'] !== 'trash';
        });
    } elseif ($tab === 'sent') {
        $messages = array_filter($messages, function($m) use ($user) {
            return $m['sender_id'] === $user['id'];
        });
    } elseif ($tab === 'trash') {
        $messages = array_filter($messages, function($m) use ($user) {
            return $m['folder'] === 'trash' && ($m['recipient_id'] === $user['id'] || $m['sender_id'] === $user['id']);
        });
    }

    $messages = array_reverse(array_values($messages)); // Latest first
    $total = count($messages);
    $offset = ($page - 1) * ITEMS_PER_PAGE;
    $page_messages = array_slice($messages, $offset, ITEMS_PER_PAGE);

    // Get sender/recipient info
    foreach ($page_messages as &$msg) {
        foreach ($users as $u) {
            if ($u['id'] === $msg['sender_id']) $msg['sender'] = $u['username'];
            if ($u['id'] === $msg['recipient_id']) $msg['recipient'] = $u['username'];
        }
    }

    echo json_encode(['messages' => $page_messages, 'total' => $total, 'page' => $page]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Social Network</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; padding: 20px; }
        .tabs { display: flex; gap: 10px; margin-bottom: 20px; }
        .tab-btn { padding: 10px 20px; background: white; border: 2px solid #ddd; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .tab-btn.active { background: #667eea; color: white; border-color: #667eea; }
        .compose-form { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .compose-form input,
        .compose-form textarea { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .compose-form textarea { resize: vertical; min-height: 100px; }
        .compose-form button { background: #667eea; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .messages-list { background: white; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .message-item { padding: 15px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .message-item:last-child { border-bottom: none; }
        .message-item:hover { background: #f9f9f9; }
        .message-content { flex: 1; }
        .message-from { font-weight: bold; color: #333; }
        .message-text { color: #666; margin-top: 5px; }
        .message-date { color: #999; font-size: 12px; margin-top: 5px; }
        .message-actions { display: flex; gap: 10px; }
        .btn-delete { background: #dc3545; color: white; padding: 6px 12px; border: none; border-radius: 5px; cursor: pointer; font-size: 12px; }
        .btn-delete:hover { background: #c82333; }
        .btn-delete-all { background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-bottom: 20px; }
        .pagination { margin-top: 20px; text-align: center; }
        .pagination button { margin: 0 5px; padding: 8px 15px; }
        .back-link { display: inline-block; margin-top: 20px; color: #667eea; text-decoration: none; }
    </style>
</head>
<body>
    <header>
        <h1>Messages</h1>
    </header>
    <div class="container">
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('inbox')">Inbox</button>
            <button class="tab-btn" onclick="switchTab('sent')">Sent</button>
            <button class="tab-btn" onclick="switchTab('trash')">Trash</button>
        </div>

        <div id="inboxTab" class="tab-content">
            <div class="messages-list" id="messagesList"></div>
            <button class="btn-delete-all" onclick="deleteAll('inbox')">Delete All</button>
            <div class="pagination">
                <button onclick="prevPage()">Previous</button>
                <span id="pageInfo">Page 1</span>
                <button onclick="nextPage()">Next</button>
            </div>
        </div>

        <a href="profile.php" class="back-link">← Back to Profile</a>
    </div>

    <script>
        let currentTab = 'inbox';
        let currentPage = 1;

        function loadMessages() {
            fetch(`messages.php?action=get_messages&tab=${currentTab}&page=${currentPage}`)
                .then(r => r.json())
                .then(data => {
                    let html = '';
                    if (data.messages.length === 0) {
                        html = '<div style="padding: 20px; text-align: center; color: #999;">No messages</div>';
                    } else {
                        data.messages.forEach(msg => {
                            const otherUser = currentTab === 'sent' ? msg.recipient : msg.sender;
                            html += `
                                <div class="message-item">
                                    <div class="message-content">
                                        <div class="message-from">${otherUser}</div>
                                        <div class="message-text">${msg.content}</div>
                                        <div class="message-date">${msg.date}</div>
                                    </div>
                                    <div class="message-actions">
                                        <button class="btn-delete" onclick="deleteMessage('${msg.id}')">Delete</button>
                                    </div>
                                </div>
                            `;
                        });
                    }
                    document.getElementById('messagesList').innerHTML = html;
                    document.getElementById('pageInfo').innerText = `Page ${data.page} of ${Math.ceil(data.total / 10)}`;
                });
        }

        function switchTab(tab) {
            currentTab = tab;
            currentPage = 1;
            loadMessages();
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
        }

        function deleteMessage(messageId) {
            if (confirm('Delete this message?')) {
                fetch('messages.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=delete_message&message_id=${messageId}`
                }).then(() => loadMessages());
            }
        }

        function deleteAll(folder) {
            if (confirm('Delete all messages in this folder? This cannot be undone.')) {
                fetch('messages.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=delete_all&folder=${folder}`
                }).then(() => loadMessages());
            }
        }

        function nextPage() { currentPage++; loadMessages(); }
        function prevPage() { if (currentPage > 1) currentPage--; loadMessages(); }

        loadMessages();
    </script>
</body>
</html>