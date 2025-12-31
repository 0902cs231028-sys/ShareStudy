<?php
session_start();
require_once 'includes/db_connect.php';

// Check Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get User Info
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Fetch User Profile Pic
$stmt = $conn->prepare("SELECT profile_pic, theme FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user_data = $stmt->fetch();
$profile_pic = $user_data['profile_pic'] ? 'uploads/avatars/' . $user_data['profile_pic'] : 'images/default_avatar.png';
$theme = $user_data['theme'];

// --- SEARCH LOGIC ---
$search_query = "";
$sql = "SELECT f.*, u.username as uploader FROM files f JOIN users u ON f.user_id = u.id";

if (isset($_GET['q']) && !empty($_GET['q'])) {
    $search_query = trim($_GET['q']);
    // Search in Filename OR Description
    $sql .= " WHERE f.file_name LIKE :q OR f.description LIKE :q";
    $sql .= " ORDER BY f.uploaded_at DESC";
    $stmt_files = $conn->prepare($sql);
    $stmt_files->execute(['q' => "%$search_query%"]);
} else {
    $sql .= " ORDER BY f.uploaded_at DESC";
    $stmt_files = $conn->query($sql);
}
$files = $stmt_files->fetchAll();
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | ShareStudy</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom sticky-top px-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="#"><i class="fas fa-graduation-cap"></i> ShareStudy</a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <form class="d-flex mx-auto my-2 my-lg-0 w-50" action="dashboard.php" method="GET">
                <input class="form-control rounded-pill" type="search" name="q" placeholder="Search files, notes, topics..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button class="btn btn-outline-primary rounded-pill ms-2" type="submit">Search</button>
            </form>

            <ul class="navbar-nav align-items-center">
                <li class="nav-item me-3">
                    <button id="themeToggle" class="btn btn-sm btn-outline-secondary rounded-circle"><i class="fas fa-moon"></i></button>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                        <img src="<?php echo htmlspecialchars($profile_pic); ?>" class="rounded-circle border" width="40" height="40" style="object-fit:cover;">
                        <span class="ms-2 fw-bold"><?php echo htmlspecialchars($username); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end glass-panel">
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#settingsModal"><i class="fas fa-cog"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid py-4">
    <div class="row g-4">
        
        <div class="col-lg-8">
            
            <div class="glass-panel p-4 mb-4">
                <h4><i class="fas fa-cloud-upload-alt"></i> Share Resources</h4>
                <div class="upload-area" id="dropZone">
                    <p class="mb-1">Drag & Drop files here or click to upload</p>
                    <small class="text-muted">(PDF, DOCX, JPG - Max 10MB)</small>
                    <input type="file" id="fileInput" hidden>
                </div>
                <div id="uploadDetails" class="mt-3 d-none">
                    <input type="text" id="fileDesc" class="form-control mb-2" placeholder="Add tags/keywords (e.g. Physics, Unit 1, Notes)">
                    <button class="btn btn-primary w-100" id="confirmUploadBtn">Upload Now</button>
                </div>
                <div id="uploadStatus" class="mt-2 text-center"></div>
            </div>

            <div class="glass-panel p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4><i class="fas fa-folder-open"></i> Recent Files</h4>
                    <?php if(!empty($search_query)): ?>
                        <span class="badge bg-warning text-dark">Filtering: "<?php echo htmlspecialchars($search_query); ?>"</span>
                    <?php endif; ?>
                </div>

                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
                    <?php if(count($files) > 0): ?>
                        <?php foreach($files as $file): ?>
                            <div class="col">
                                <div class="card h-100 glass-panel file-card border-0 p-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-file-alt file-icon me-3"></i>
                                        <div>
                                            <h6 class="mb-0 text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($file['file_name']); ?></h6>
                                            <small class="text-muted" style="font-size:0.75rem;">
                                                By <?php echo htmlspecialchars($file['uploader']); ?> • <?php echo round($file['file_size']/1024); ?> KB
                                            </small>
                                        </div>
                                    </div>
                                    <p class="small text-muted mb-2 text-truncate"><?php echo htmlspecialchars($file['description']); ?></p>
                                    <div class="mt-auto d-flex justify-content-between">
                                        <a href="uploads/<?php echo $file['stored_name']; ?>" download="<?php echo $file['file_name']; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i></a>
                                        <?php if($role === 'admin' || $user_id == $file['user_id']): ?>
                                            <button class="btn btn-sm btn-outline-danger disabled"><i class="fas fa-trash"></i></button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center w-100">No files found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="glass-panel chat-container">
                <div class="p-3 border-bottom border-light">
                    <h5 class="mb-0"><i class="fas fa-comments"></i> Global Chat</h5>
                </div>
                
                <div class="chat-messages" id="chatBox">
                    <div class="text-center mt-5"><div class="spinner-border text-primary"></div></div>
                </div>

                <div class="p-3 border-top border-light">
                    <div class="input-group">
                        <input type="text" id="chatInput" class="form-control" placeholder="Type a message...">
                        <button class="btn btn-primary" id="sendChatBtn"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="settingsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content glass-panel">
            <div class="modal-header border-0">
                <h5 class="modal-title">Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Change Profile Picture</h6>
                <form id="avatarForm">
                    <input type="file" name="avatar" class="form-control mb-2" accept="image/*">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Update Photo</button>
                </form>
                <div id="avatarStatus" class="mt-2 text-center small"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // --- 1. THEME TOGGLE ---
    const themeBtn = document.getElementById('themeToggle');
    const htmlEl = document.documentElement;
    
    // Load saved theme
    const savedTheme = localStorage.getItem('theme') || '<?php echo $theme; ?>';
    htmlEl.setAttribute('data-theme', savedTheme);

    themeBtn.addEventListener('click', () => {
        const current = htmlEl.getAttribute('data-theme');
        const newTheme = current === 'dark' ? 'light' : 'dark';
        htmlEl.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        // Optional: Save to DB via AJAX so it persists on other devices
    });

    // --- 2. FILE UPLOAD (Drag & Drop) ---
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const uploadDetails = document.getElementById('uploadDetails');
    const confirmBtn = document.getElementById('confirmUploadBtn');
    let selectedFile = null;

    dropZone.addEventListener('click', () => fileInput.click());
    
    fileInput.addEventListener('change', (e) => {
        if(e.target.files.length) handleFileSelect(e.target.files[0]);
    });

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.style.background = 'rgba(102, 126, 234, 0.2)';
    });
    dropZone.addEventListener('dragleave', () => dropZone.style.background = '');
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.style.background = '';
        if(e.dataTransfer.files.length) handleFileSelect(e.dataTransfer.files[0]);
    });

    function handleFileSelect(file) {
        selectedFile = file;
        dropZone.innerHTML = `<p class="fw-bold text-success">${file.name}</p>`;
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

        fetch('upload_file.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                document.getElementById('uploadStatus').innerHTML = '<span class="text-success">Success! Reloading...</span>';
                setTimeout(() => location.reload(), 1000);
            } else {
                document.getElementById('uploadStatus').innerHTML = `<span class="text-danger">${data.message}</span>`;
                confirmBtn.innerHTML = 'Upload Now';
                confirmBtn.disabled = false;
            }
        });
    });

    // --- 3. GLOBAL CHAT ENGINE ---
    const chatBox = document.getElementById('chatBox');
    const chatInput = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendChatBtn');
    let userRole = '<?php echo $role; ?>';
    let currentUserId = <?php echo $user_id; ?>;

    function fetchMessages() {
        fetch('message.php?action=fetch')
        .then(res => res.json())
        .then(res => {
            if(res.status === 'success') {
                let html = '';
                res.data.forEach(msg => {
                    const isMine = (msg.user_id == currentUserId);
                    const align = isMine ? 'my-message' : '';
                    
                    // Admin Delete Button
                    let delBtn = '';
                    if(userRole === 'admin' || isMine) {
                        delBtn = `<i class="fas fa-times text-danger ms-2" style="cursor:pointer; font-size:0.7rem;" onclick="deleteMsg(${msg.id})"></i>`;
                    }

                    html += `
                    <div class="chat-message ${align}">
                        <div class="small text-muted" style="font-size:0.7rem;">
                            ${msg.username} <span style="font-size:0.6rem;">${msg.time_formatted}</span>
                        </div>
                        <div class="message-bubble">
                            ${msg.message} ${delBtn}
                        </div>
                    </div>`;
                });
                chatBox.innerHTML = html;
            }
        });
    }

    // Send Message
    sendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', (e) => { if(e.key === 'Enter') sendMessage(); });

    function sendMessage() {
        const msg = chatInput.value.trim();
        if(!msg) return;

        const formData = new FormData();
        formData.append('action', 'send');
        formData.append('message', msg);

        fetch('message.php', { method: 'POST', body: formData })
        .then(() => {
            chatInput.value = '';
            fetchMessages(); // Refresh instantly
            chatBox.scrollTop = chatBox.scrollHeight;
        });
    }

    // Delete Message
    window.deleteMsg = function(id) {
        if(!confirm('Delete this message?')) return;
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('msg_id', id);
        fetch('message.php', { method: 'POST', body: formData })
        .then(() => fetchMessages());
    };

    // Auto-refresh chat every 3 seconds
    setInterval(fetchMessages, 3000);
    fetchMessages(); // Initial load

    // --- 4. PROFILE PIC UPLOAD ---
    const avatarForm = document.getElementById('avatarForm');
    avatarForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(avatarForm);
        
        fetch('upload_profile_pic.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                document.getElementById('avatarStatus').innerHTML = '<span class="text-success">Updated!</span>';
                setTimeout(() => location.reload(), 1000);
            } else {
                document.getElementById('avatarStatus').innerHTML = `<span class="text-danger">${data.message}</span>`;
            }
        });
    });

</script>
</body>
</html>
