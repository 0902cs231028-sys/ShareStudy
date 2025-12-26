<?php
session_start();
require_once 'includes/db_connect.php';

// Return JSON for smooth frontend handling
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
$max_size = 2 * 1024 * 1024; // 2MB Limit for avatars (plenty for profile pics)

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {

    $file = $_FILES['avatar'];
    $file_name = $file['name'];
    $file_tmp  = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];

    // Get extension
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // 1. Validate
    if ($file_error !== 0) {
        echo json_encode(['status' => 'error', 'message' => 'Upload error.']);
        exit;
    }
    if (!in_array($file_ext, $allowed_ext)) {
        echo json_encode(['status' => 'error', 'message' => 'Only JPG, PNG, or GIF allowed.']);
        exit;
    }
    if ($file_size > $max_size) {
        echo json_encode(['status' => 'error', 'message' => 'Image too large. Max 2MB.']);
        exit;
    }

    // 2. Generate Unique Name
    $new_file_name = "user_" . $user_id . "_" . uniqid() . "." . $file_ext;
    $upload_dest = 'uploads/avatars/' . $new_file_name;

    // 3. Move File
    if (move_uploaded_file($file_tmp, $upload_dest)) {
        
        try {
            // A. Find old profile pic to delete (Save Space!)
            $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = :id");
            $stmt->execute(['id' => $user_id]);
            $current_user = $stmt->fetch();
            $old_pic = $current_user['profile_pic'];

            // Delete old pic if it exists and isn't the default one
            if ($old_pic && $old_pic !== 'default_avatar.png') {
                $old_path = 'uploads/avatars/' . $old_pic;
                if (file_exists($old_path)) {
                    unlink($old_path);
                }
            }

            // B. Update Database
            $update = $conn->prepare("UPDATE users SET profile_pic = :pic WHERE id = :id");
            $update->execute(['pic' => $new_file_name, 'id' => $user_id]);

            // C. Update Session immediately (so header updates instantly)
            $_SESSION['profile_pic'] = $new_file_name; // Assuming you store this in session on login

            echo json_encode(['status' => 'success', 'new_url' => $upload_dest]);

        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error.']);
        }

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save image.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No file sent.']);
}
?>
