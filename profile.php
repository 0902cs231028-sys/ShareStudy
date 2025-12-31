<?php
session_start();
require_once 'includes/db_connect.php';

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. Fetch User Details Dynamically
try {
    $stmt = $conn->prepare("SELECT username, email, role, profile_pic, theme FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $u = $stmt->fetch();

    if (!$u) {
        session_destroy();
        header("Location: login.php");
        exit;
    }

    $profile_pic = $u['profile_pic'] ? 'uploads/avatars/' . $u['profile_pic'] : 'images/default_avatar.png';
    $theme = $u['theme'] ?? 'dark';

    // 3. Dynamic Upload Counter
    $count_stmt = $conn->prepare("SELECT COUNT(*) FROM files WHERE user_id = :uid");
    $count_stmt->execute(['uid' => $user_id]);
    $upload_count = $count_stmt->fetchColumn();

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | ShareStudy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: radial-gradient(circle at top left, #0f172a, #1e293b); min-height: 100vh; color: #f8fafc; font-family: 'Inter', sans-serif; }
        .profile-card { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 24px; padding: 2.5rem; margin-top: 50px; box-shadow: 0 20px 50px rgba(0,0,0,0.3); }
        .avatar-wrapper { position: relative; width: 150px; height: 150px; margin: 0 auto 1.5rem; }
        .profile-avatar { width: 150px; height: 150px; object-fit: cover; border-radius: 50%; border: 4px solid #3b82f6; }
        .upload-overlay { position: absolute; bottom: 5px; right: 5px; background: #3b82f6; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 3px solid #0f172a; color: white; }
        .stat-box { background: rgba(255, 255, 255, 0.05); border-radius: 16px; padding: 1.25rem; text-align: center; }
        .info-label { color: #94a3b8; font-size: 0.85rem; text-transform: uppercase; }
        .back-btn { color: #94a3b8; text-decoration: none; }
        .modal-content { background: #1e293b; color: white; border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="profile-card text-center">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <a href="dashboard.php" class="back-btn small"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
                    <span class="badge <?php echo ($u['role'] === 'admin') ? 'bg-warning text-dark' : 'bg-primary'; ?> text-uppercase px-3 py-2">
                        <?php echo htmlspecialchars($u['role']); ?>
                    </span>
                </div>

                <div class="avatar-wrapper">
                    <img src="<?php echo htmlspecialchars($profile_pic); ?>" id="currentAvatar" class="profile-avatar" alt="Profile">
                    <label for="avatarInput" class="upload-overlay"><i class="fas fa-camera"></i></label>
                    <input type="file" id="avatarInput" hidden accept="image/*">
                </div>

                <h3 class="fw-bold mb-1"><?php echo htmlspecialchars($u['username']); ?></h3>
                <p class="text-muted small mb-4"><?php echo htmlspecialchars($u['email']); ?></p>

                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="stat-box">
                            <div class="info-label">Resources Shared</div>
                            <div class="display-6 fw-bold text-primary"><?php echo $upload_count; ?></div>
                        </div>
                    </div>
                </div>

                <hr class="border-secondary opacity-25 my-4">

                <div class="text-start">
                    <h6 class="mb-3 text-uppercase small text-muted fw-bold">Account Settings</h6>
                    
                    <button class="btn btn-outline-light w-100 mb-2 border-0 text-start py-3" style="background: rgba(255,255,255,0.02);" data-bs-toggle="modal" data-bs-target="#passModal">
                        <i class="fas fa-lock me-3 text-primary"></i> Change Password
                    </button>
                    
                    <?php if($u['role'] === 'admin'): ?>
                    <a href="admin/dashboard.php" class="btn btn-outline-warning w-100 mb-2 border-0 text-start py-3" style="background: rgba(255,255,255,0.02);">
                        <i class="fas fa-user-shield me-3"></i> Admin Command Center
                    </a>
                    <?php endif; ?>

                    <a href="logout.php" class="btn btn-danger w-100 mt-4 py-3 rounded-pill fw-bold">
                        <i class="fas fa-sign-out-alt me-2"></i> LOGOUT SESSION
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="passModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <h5 class="fw-bold mb-3">Update Security</h5>
            <div class="mb-3">
                <label class="small text-muted">Current Password</label>
                <input type="password" id="oldPass" class="form-control bg-dark text-white border-secondary">
            </div>
            <div class="mb-4">
                <label class="small text-muted">New Password</label>
                <input type="password" id="newPass" class="form-control bg-dark text-white border-secondary">
            </div>
            <button class="btn btn-primary w-100 fw-bold py-2" id="savePassBtn">Save Changes</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // --- 1. Password Change Logic ---
    document.getElementById('savePassBtn').onclick = function() {
        const oldP = document.getElementById('oldPass').value;
        const newP = document.getElementById('newPass').value;
        
        if(!oldP || !newP) {
            alert("Please fill in both fields.");
            return;
        }

        const fd = new FormData();
        fd.append('old_password', oldP);
        fd.append('new_password', newP);

        fetch('change_password.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if(data.status === 'success') location.reload();
        });
    };

     // --- 2. INSTANT Profile Picture Upload Logic ---
document.getElementById('avatarInput').onchange = function(e) {
    if (!this.files || !this.files[0]) return;
    
    const selectedFile = this.files[0];
    const avatarImg = document.getElementById('currentAvatar');

    // 1. INSTANT PREVIEW (Local Cache Trick)
    // This makes the UI update immediately before the server even sees the file
    const objectURL = URL.createObjectURL(selectedFile);
    const originalSrc = avatarImg.src; // Backup current pic for error recovery
    avatarImg.src = objectURL; 
    avatarImg.style.opacity = '0.6'; // Visual hint that it is "syncing"

    // 2. BACKGROUND UPLOAD
    const formData = new FormData();
    formData.append('avatar', selectedFile);

    // Connecting to your provided upload_profile_pic.php
    fetch('upload_profile_pic.php', { 
        method: 'POST', 
        body: formData 
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Upload finished successfully in background
            avatarImg.style.opacity = '1';
            URL.revokeObjectURL(objectURL); // Free up browser memory
            console.log("Profile picture synchronized with server.");
        } else {
            // Revert UI if the server rejected the file
            avatarImg.src = originalSrc;
            avatarImg.style.opacity = '1';
            alert('Upload failed: ' + data.message);
        }
    })
    .catch(error => {
        // Revert UI if there is a network/connection error
        avatarImg.src = originalSrc;
        avatarImg.style.opacity = '1';
        console.error('Error:', error);
    });
};
    
</script>
</body>
</html>
