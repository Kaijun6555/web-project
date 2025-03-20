<?php
session_start();
require 'db-connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order_id'], $_POST['message'])) {
    $order_id = (int) $_POST['order_id'];
    $message = trim($_POST['message']);
    $sender = 'Customer'; // Modify for authentication system

    if (!empty($message) && $order_id > 0) {
        $stmt = $conn->prepare("INSERT INTO chat (order_id, sender, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $order_id, $sender, $message);
        $stmt->execute();
        $stmt->close();
    }
}
?>
