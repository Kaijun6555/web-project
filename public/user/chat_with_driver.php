<!-- chat_with_driver.php -->
<?php
session_start();
require '../../db/db-connect.php';

// Check if order ID is provided
$order_id = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
if ($order_id <= 0) {
    die("Invalid order ID.");
}

// Fetch driver assigned to the order
$stmt = $conn->prepare("SELECT delivery_user_id FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order || !$order['delivery_user_id']) {
    die("No driver assigned to this order yet.");
}
$delivery_user_id = $order['delivery_user_id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Food</title>
    <?php include '../inc/head.inc.php'; ?>
</head>

<body>
    <?php include '../inc/nav.inc.php'; ?>
    <div class="container mt-4">
        <h2>Chat with Your Driver</h2>
        <div id="chat-box" class="chat-box border p-3 mb-3" style="height: 300px; overflow-y: scroll;">
            <!-- Chat messages will be loaded here dynamically -->
        </div>

        <form id="chat-form" method="POST">
            <div class="mb-3">
                <textarea id="chat-message" name="message" class="form-control" placeholder="Type your message..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send</button>
        </form>
    </div>

    <body>

</html>
<script>
    function loadChat() {
        fetch("../../db/load_chat.php?order_id=<?= $order_id ?>")
            .then(response => response.text())
            .then(data => {
                document.getElementById("chat-box").innerHTML = data;
                document.getElementById("chat-box").scrollTop = document.getElementById("chat-box").scrollHeight;
            });
    }

    // Auto-refresh chat every 3 seconds
    setInterval(loadChat, 3000);

    // Load chat initially
    loadChat();

    // Handle message sending with AJAX
    document.getElementById("chat-form").addEventListener("submit", function(e) {
        e.preventDefault();
        let message = document.getElementById("chat-message").value;

        fetch("../../db/send_chat.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `order_id=<?= $order_id ?>&message=${encodeURIComponent(message)}`
        }).then(response => {
            document.getElementById("chat-message").value = "";
            loadChat();
        });
    });
</script>

<?php include '../inc/footer.inc.php'; ?>