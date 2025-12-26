<?php
session_start();
require_once '../includes/db_connect.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$profile_pic = $_SESSION['profile_pic'] ?? 'default_avatar.png'; // Get latest pic from session

// 2. Handle Actions
$msg = "";
if (isset($_GET['action']) && isset($_GET['id'])) {
    $target_id = $_GET['id'];
    
    if ($_GET['action'] == 'ban') {
        $stmt = $conn->prepare("UPDATE users SET is_blocked = 1 WHERE id = :id AND role != 'admin'");
        $stmt->execute(['id' => $target_id]);
        $msg = "User Banned.";
    }
    if ($_GET['action'] == 'unban') {
        $stmt = $conn->prepare("UPDATE users SET is_blocked = 0 WHERE id = :id");
        $stmt->execute(['id' => $target_id]);
        $msg = "User Unbanned.";
    }
    if ($_GET['action'] == 'delete_file') {
        $stmt = $conn->prepare("SELECT stored_name FROM files WHERE id = :id");
        $stmt->execute(['id' => $target_id]);
        $file = $stmt->fetch();
        if ($file) {
            $path = "../uploads/" . $file['stored_name'];
            if (file_exists($path)) unlink($path);
            $conn->prepare("DELETE FROM files WHERE id = :id")->execute(['id' => $target_id]);
            $msg = "File Deleted.";
        }
    }
}

// 3. Fetch Stats & Lists
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_files = $conn->query("SELECT COUNT(*) FROM files")->fetchColumn();
$total_chats = $conn->query("SELECT COUNT(*) FROM chat_messages")->fetchColumn();
$users_list = $conn->query("SELECT * FROM users WHERE role != 'admin' ORDER BY id DESC")->fetchAll();
$files_list = $conn->query("SELECT f.*, u.username FROM files f JOIN users u ON f.user_id = u.id ORDER BY f.id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Command | ShareStudy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d); }
        .stat-card { background: rgba(0,0,0,0.5); border-left: 5px solid gold; }
        .table-glass { background: rgba(255,255,255,0.1); color: white; border-radius: 10px; }
        .table-glass th, .table-glass td { color: white; border-color: rgba(255,255,255,0.1); }
        .admin-badge { background: gold; color: black; font-weight: bold; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; font-size: 0.7rem; }
        .upload-area { border: 2px dashed gold; background: rgba(0,0,0,0.2); cursor: pointer; color: white; }
        .upload-area:hover { background: rgba(255, 215, 0, 0.1); }
        /* Cursor pointer for avatar to show it's clickable */
        .admin-avatar-pulse { cursor: pointer; transition: transform 0.2s; }
        .admin-avatar-pulse:hover { transform: scale(1.1); }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark px-4 border-bottom border-secondary">
    <a class="navbar-brand" href="#"><i class="fas fa-shield-alt text-warning"></i> ADMIN PANEL</a>
    <div class="d-flex align-items-center">
        <div class="me-3 text-end">
            <span class="d-block fw-bold text-warning"><?php echo htmlspecialchars($username); ?></span>
            <span class="admin-badge">Super Admin</span>
        </div>
        <img src="../uploads/avatars/<?php echo $profile_pic; ?>" 
             class="rounded-circle admin-avatar-pulse" width="50" height="50" style="object-fit:cover;"
             data-bs-toggle="modal" data-bs-target="#adminSettingsModal" title="Change Profile Pic">
        
        <a href="../logout.php" class="btn btn-sm btn-outline-light ms-4">Logout</a>
    </div>
</nav>

<div class="container-fluid py-4">
    <?php if($msg): ?><div class="alert alert-success text-center"><?php echo $msg; ?></div><?php endif; ?>

    <div class="row g-4 mb-4">
        <div class="col-md-4"><div class="card stat-card p-3"><h3 class="fw-bold text-white"><?php echo $total_users; ?></h3><span class="text-warning">Users</span></div></div>
        <div class="col-md-4"><div class="card stat-card p-3" style="border-left-color: cyan;"><h3 class="fw-bold text-white"><?php echo $total_files; ?></h3><span class="text-info">Files</span></div></div>
        <div class="col-md-4"><div class="card stat-card p-3" style="border-left-color: lime;"><h3 class="fw-bold text-white"><?php echo $total_chats; ?></h3><span class="text-success">Chats</span></div></div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="glass-panel p-4">
                <h5 class="text-warning"><i class="fas fa-upload"></i> Admin Upload</h5>
                <div class="upload-area text-center p-3" id="dropZone">
                    <p class="mb-0">Drag & Drop file to upload as Admin</p>
                    <input type="file" id="fileInput" hidden>
                </div>
                <div id="uploadDetails" class="mt-2 d-none">
                    <input type="text" id="fileDesc" class="form-control bg-dark text-white border-secondary" placeholder="Description">
                    <button class="btn btn-warning w-100 mt-2" id="confirmUploadBtn">Upload</button>
                </div>
                <div id="uploadStatus" class="mt-2 text-center text-white"></div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="glass-panel p-4 h-100">
                <h4 class="mb-3"><i class="fas fa-users"></i> Users</h4>
                <div class="table-responsive" style="max-height: 400px; overflow-y:auto;">
                    <table class="table table-glass table-hover">
                        <thead><tr><th>User</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php foreach($users_list as $u): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                <td><?php echo $u['is_blocked'] ? '<span class="badge bg-danger">Banned</span>' : '<span class="badge bg-success">Active</span>'; ?></td>
                                <td>
                                    <?php if($u['is_blocked']): ?>
                                        <a href="dashboard.php?action=unban&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-light">Unban</a>
                                    <?php else: ?>
                                        <a href="dashboard.php?action=ban&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Ban user?')">Ban</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="glass-panel p-4 h-100">
                <h4 class="mb-3"><i class="fas fa-file-archive"></i> Files</h4>
                <div class="table-responsive" style="max-height: 400px; overflow-y:auto;">
                    <table class="table table-glass table-hover">
                        <thead><tr><th>File</th><th>Downloads</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php foreach($files_list as $f): ?>
                            <tr>
                                <td class="text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($f['file_name']); ?></td>
                                <td><?php echo $f['downloads'] ?? 0; ?></td>
                                <td>
                                    <a href="../uploads/<?php echo $f['stored_name']; ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                                    <a href="dashboard.php?action=delete_file&id=<?php echo $f['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete permanently?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="glass-panel p-4">
                <h4><i class="fas fa-comments"></i> Chat Monitor</h4>
                <div class="chat-container" style="height: 300px;">
                    <div class="chat-messages" id="chatBox"></div>
                    <div class="mt-2 d-flex">
                        <input type="text" id="chatInput" class="form-control me-2" placeholder="Admin Announcement...">
                        <button class="btn btn-warning fw-bold" id="sendChatBtn">SEND</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="adminSettingsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content glass-panel" style="background: #2c3e50; color: white;">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Update Admin Avatar</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="adminAvatarForm">
                    <input type="file" name="avatar" class="form-control bg-dark text-white border-secondary mb-3" accept="image/*" required>
                    <button type="submit" class="btn btn-warning w-100">Update Profile Pic</button>
                </form>
                <div id="adminAvatarStatus" class="mt-2 text-center"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // --- 1. ADMIN UPLOAD ---
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const uploadDetails = document.getElementById('uploadDetails');
    const confirmBtn = document.getElementById('confirmUploadBtn');
    let selectedFile = null;

    dropZone.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', (e) => { if(e.target.files.length) handleFileSelect(e.target.files[0]); });
    dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.style.background = 'rgba(255,215,0,0.2)'; });
    dropZone.addEventListener('dragleave', () => dropZone.style.background = '');
    dropZone.addEventListener('drop', (e) => { e.preventDefault(); dropZone.style.background = ''; if(e.dataTransfer.files.length) handleFileSelect(e.dataTransfer.files[0]); });

    function handleFileSelect(file) {
        selectedFile = file;
        dropZone.innerHTML = `<p class="fw-bold text-warning">${file.name}</p>`;
        uploadDetails.classList.remove('d-none');
    }

    confirmBtn.addEventListener('click', () => {
        if(!selectedFile) return;
        const desc = document.getElementById('fileDesc').value;
        const formData = new FormData();
        formData.append('file', selectedFile);
        formData.append('description', desc);

        confirmBtn.innerHTML = 'Uploading...';
        confirmBtn.disabled = true;

        fetch('../upload_file.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                document.getElementById('uploadStatus').innerHTML = '<span class="text-success">Success! Reloading...</span>';
                setTimeout(() => location.reload(), 1000);
            } else {
                document.getElementById('uploadStatus').innerHTML = `<span class="text-danger">${data.message}</span>`;
                confirmBtn.innerHTML = 'Upload';
                confirmBtn.disabled = false;
            }
        });
    });

    // --- 2. ADMIN AVATAR UPDATE ---
    const adminAvatarForm = document.getElementById('adminAvatarForm');
    adminAvatarForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(adminAvatarForm);
        
        // IMPORTANT: We use '../upload_profile_pic.php' because we are in the admin folder
        fetch('../upload_profile_pic.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                document.getElementById('adminAvatarStatus').innerHTML = '<span class="text-success">Updated! Reloading...</span>';
                setTimeout(() => location.reload(), 1000);
            } else {
                document.getElementById('adminAvatarStatus').innerHTML = `<span class="text-danger">${data.message}</span>`;
            }
        });
    });

    // --- 3. CHAT MONITOR ---
    const chatBox = document.getElementById('chatBox');
    const chatInput = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendChatBtn');

    function fetchMessages() {
        fetch('../chat_backend.php?action=fetch')
        .then(res => res.json())
        .then(res => {
            if(res.status === 'success') {
                let html = '';
                res.data.forEach(msg => {
                    const isAdmin = (msg.role === 'admin');
                    const bubbleClass = isAdmin ? 'admin-message-bubble' : 'message-bubble';
                    const delBtn = `<i class="fas fa-times text-danger ms-2" style="cursor:pointer;" onclick="deleteMsg(${msg.id})"></i>`;
                    html += `
                    <div class="chat-message">
                        <small class="text-light">${msg.username} ${isAdmin ? '<i class="fas fa-star text-warning"></i>' : ''}</small>
                        <div class="${bubbleClass} p-2 rounded d-inline-block ms-2">${msg.message} ${delBtn}</div>
                    </div>`;
                });
                chatBox.innerHTML = html;
            }
        });
    }

    sendBtn.addEventListener('click', () => {
        const msg = chatInput.value.trim();
        if(!msg) return;
        const formData = new FormData();
        formData.append('action', 'send');
        formData.append('message', msg);
        fetch('../chat_backend.php', { method: 'POST', body: formData })
        .then(() => { chatInput.value = ''; fetchMessages(); });
    });

    window.deleteMsg = function(id) {
        if(!confirm('Delete?')) return;
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('msg_id', id);
        fetch('../chat_backend.php', { method: 'POST', body: formData })
        .then(() => fetchMessages());
    };

    setInterval(fetchMessages, 3000);
    fetchMessages();
</script>
</body>
</html>
