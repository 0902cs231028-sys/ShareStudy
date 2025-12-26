<?php
session_start();
require_once 'includes/db_connect.php';

// Output JSON so our Drag-and-Drop interface can read it
header('Content-Type: application/json');

// 1. Security & Setup
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Define allowed file types
$allowed_ext = ['pdf', 'docx', 'pptx', 'txt', 'epub', 'jpg', 'jpeg', 'png'];

// Maximum file size (InfinityFree limit is usually 10MB-12MB, so we set limit to 10MB)
$max_size = 10 * 1024 * 1024; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Check if file and description exist
    if (isset($_FILES['file']) && isset($_POST['description'])) {
        
        $file = $_FILES['file'];
        $desc = trim($_POST['description']);
        $file_name = $file['name'];
        $file_tmp  = $file['tmp_name'];
        $file_size = $file['size'];
        $file_error = $file['error'];

        // Get extension
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // 2. Validation
        if ($file_error !== 0) {
            echo json_encode(['status' => 'error', 'message' => 'Upload error code: ' . $file_error]);
            exit;
        }

        if (!in_array($file_ext, $allowed_ext)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only PDF, DOCX, PPTX, EPUB, Images allowed.']);
            exit;
        }

        if ($file_size > $max_size) {
            echo json_encode(['status' => 'error', 'message' => 'File too large. Max limit is 10MB.']);
            exit;
        }

        // 3. Secure Renaming (Crucial for hosting)
        // We allow the user to keep the original name for display, but on the server
        // we save it as "uniqid_name" to prevent overwriting.
        $new_file_name = uniqid('', true) . "." . $file_ext;
        $upload_dest = 'uploads/' . $new_file_name;

        // 4. Move File & Save to DB
        if (move_uploaded_file($file_tmp, $upload_dest)) {
            
            try {
                $sql = "INSERT INTO files (user_id, file_name, stored_name, file_type, file_size, description) 
                        VALUES (:uid, :fname, :sname, :ftype, :fsize, :desc)";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    'uid'   => $_SESSION['user_id'],
                    'fname' => $file_name,     // The real name (Math_Homework.pdf)
                    'sname' => $new_file_name, // The safe name (65a3b2_... .pdf)
                    'ftype' => $file_ext,
                    'fsize' => $file_size,
                    'desc'  => $desc           // The tags for searching!
                ]);

                echo json_encode(['status' => 'success', 'message' => 'File uploaded successfully!']);

            } catch (PDOException $e) {
                // If DB fails, remove the uploaded file to keep folder clean
                unlink($upload_dest);
                echo json_encode(['status' => 'error', 'message' => 'Database error.']);
            }

        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to move file. Folder permission issue?']);
        }

    } else {
        echo json_encode(['status' => 'error', 'message' => 'No file selected.']);
    }
}
?>
