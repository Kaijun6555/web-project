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

$merchantId = (int) $_POST['id'];

// Prepare the UPDATE query, updating only if approval is still pending (approval = 0 or approval IS NULL)
$query = "UPDATE restaurant SET approval = 1 WHERE idrestaurant = ? AND (approval = 0 OR approval IS NULL)";
$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $merchantId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Merchant approved.']);
    } else {
        // This might occur if the merchant was already approved/rejected or the ID is invalid.
        echo json_encode(['success' => false, 'message' => 'No row updated. Merchant may already be approved or the ID is invalid.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Execution failed: ' . $stmt->error]);
}
$stmt->close();
?>
