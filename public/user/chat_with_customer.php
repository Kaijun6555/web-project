<?php
session_start();
require '../../db/db-connect.php';

// Initialize variables
$error_message = null;
$driver_id = $_SESSION['driver_id'] ?? null;

// Check if driver is logged in
if (!$driver_id) {
    $error_message = "Access denied. Please log in as a driver.";
}

// Validate order ID
$order_id = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
if (!$error_message && $order_id <= 0) {
    $error_message = "Invalid order ID.";
}

// Check driver assigned to order
if (!$error_message) {
    $stmt = $conn->prepare("SELECT customer_user_id FROM Orders WHERE idOrders = ? AND delivery_user_id = ?");
    $stmt->bind_param("ii", $order_id, $driver_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();

    if (!$order || !$order['customer_user_id']) {
        $error_message = "You're not assigned to this order, or no customer found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Chat with Customer</title>
    <?php include '../inc/head.inc.php'; ?>
</head>
<body>
    <?php include '../inc/nav.inc.php'; ?>

    <div class="container mt-4">
        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <h4 class="alert-heading">Error</h4>
                <p><?= htmlspecialchars($error_message) ?></p>
                <hr>
                <button onclick="history.back()" class="btn btn-secondary">Go Back</button>
            </div>
        <?php else: ?>
            <h2>Chat with Customer</h2>
            <div id="chat-box" class="chat-box border p-3 mb-3" style="height: 300px; overflow-y: scroll;">
                <!-- Chat messages will be loaded here dynamically -->
            </div>

            <form id="chat-form" method="POST">
                <div class="mb-3">
                    <textarea id="chat-message" name="message" class="form-control" placeholder="Type your message..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send</button>
            </form>
        <?php endif; ?>
    </div>

<?php include '../inc/footer.inc.php'; ?>
</body>
</html>

<?php if (!$error_message): ?>
<script>
    function loadChat() {
        fetch("/user/load_chat.php?order_id=<?= $order_id ?>")
            .then(response => response.text())
            .then(data => {
                const chatBox = document.getElementById("chat-box");
                chatBox.innerHTML = data;
                chatBox.scrollTop = chatBox.scrollHeight;
            });
    }

    setInterval(loadChat, 3000);
    loadChat();

    document.getElementById("chat-form").addEventListener("submit", function (e) {
        e.preventDefault();
        let message = document.getElementById("chat-message").value;

        fetch("/user/send_chat.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `order_id=<?= $order_id ?>&message=${encodeURIComponent(message)}&sender=driver`
        }).then(response => {
            document.getElementById("chat-message").value = "";
            loadChat();
        });
    });
</script>
<?php endif; ?>
