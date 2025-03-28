<?php
session_start();
header('Content-Type: application/json');
require '../../../db/db-connect.php';

// Ensure the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Ensure the merchant ID is provided
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing merchant ID']);
    exit;
}

$merchantId = $_POST['id'];

// Update the approval status to rejected (2)
$stmt = $conn->prepare("UPDATE restaurant SET approval = 2 WHERE idrestaurant = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}
$stmt->bind_param("i", $merchantId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Execution failed: ' . $stmt->error]);
}

$stmt->close();
?>
