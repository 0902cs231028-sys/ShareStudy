<?php
session_start();
require_once 'includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Clear the pending warning text for the current user
    $stmt = $conn->prepare("UPDATE users SET pending_warning = NULL WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    
    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to clear warning']);
}
?>
