<?php
session_start();
require_once 'includes/db_connect.php';

// Return JSON for the dashboard's JavaScript
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Login required']);
    exit;
}

if (isset($_GET['id'])) {
    $file_id = (int)$_GET['id'];
    
    try {
        // Increase the report count for this file
        $stmt = $conn->prepare("UPDATE files SET report_count = report_count + 1 WHERE id = :id");
        $stmt->execute(['id' => $file_id]);
        
        echo json_encode(['status' => 'success', 'message' => 'Content reported to admin.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No file ID provided']);
}
?>
