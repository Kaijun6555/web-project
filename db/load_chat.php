<?php
require 'db-connect.php';

$order_id = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
if ($order_id <= 0) exit;

$stmt = $conn->prepare("SELECT sender, message, created_at FROM chat WHERE order_id = ? ORDER BY created_at ASC");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

while ($chat = $result->fetch_assoc()) {
    echo "<p><strong>" . htmlspecialchars($chat['sender']) . ":</strong> " . htmlspecialchars($chat['message']) . " <br>";
    echo "<small class='text-muted'>[" . htmlspecialchars($chat['created_at']) . "]</small></p>";
}
$stmt->close();
?>
