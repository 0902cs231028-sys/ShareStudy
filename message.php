<?php
session_start();
require_once 'includes/db_connect.php';

// Tell the browser we are sending JSON data back
header('Content-Type: application/json');

// 1. Security Check: Must be logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in.']);
    exit;
}

$current_user_id = $_SESSION['user_id'];
$current_role    = $_SESSION['role'] ?? 'student';

// Determine the action (Send, Fetch, or Delete)
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {

    // --- ACTION: SEND MESSAGE ---
    if ($action === 'send') {
        $message = trim($_POST['message'] ?? '');
        
        if (!empty($message)) {
            // Check for potential XSS (security)
            $message = htmlspecialchars($message);

            $stmt = $conn->prepare("INSERT INTO chat_messages (user_id, message) VALUES (:uid, :msg)");
            $stmt->execute(['uid' => $current_user_id, 'msg' => $message]);
            
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Message cannot be empty']);
        }
    }

    // --- ACTION: FETCH MESSAGES ---
    elseif ($action === 'fetch') {
        // Get last 50 messages + User Info (Profile Pic, Name, Role)
        // We JOIN the tables so we know WHO wrote the message
        $sql = "SELECT m.id, m.message, m.sent_at, m.user_id, 
                       u.username, u.profile_pic, u.role 
                FROM chat_messages m 
                JOIN users u ON m.user_id = u.id 
                ORDER BY m.id ASC 
                LIMIT 50";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $messages = $stmt->fetchAll();

        // Format the time nicely for the user
        foreach ($messages as &$msg) {
            $msg['time_formatted'] = date("h:i A", strtotime($msg['sent_at']));
            // Flag if the current user owns this message (to show delete button for themselves)
            $msg['is_mine'] = ($msg['user_id'] == $current_user_id);
        }

        echo json_encode(['status' => 'success', 'data' => $messages, 'user_role' => $current_role]);
    }

    // --- ACTION: DELETE MESSAGE (Admin or Owner) ---
    elseif ($action === 'delete') {
        $msg_id = $_POST['msg_id'] ?? 0;

        if ($msg_id) {
            // First, find out who owns the message
            $check = $conn->prepare("SELECT user_id FROM chat_messages WHERE id = :id");
            $check->execute(['id' => $msg_id]);
            $msg = $check->fetch();

            if ($msg) {
                // Allow delete IF: User is Admin OR User owns the message
                if ($current_role === 'admin' || $msg['user_id'] == $current_user_id) {
                    $del = $conn->prepare("DELETE FROM chat_messages WHERE id = :id");
                    $del->execute(['id' => $msg_id]);
                    echo json_encode(['status' => 'success']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
                }
            }
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
?>
