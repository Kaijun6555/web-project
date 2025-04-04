<?php
session_start();
require '../../db/db-connect.php';

// Redirect to admin login if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /user/login.php");
    exit();
}

// Fetch pending restaurants (approval = 0 or NULL means pending)
$query = "SELECT idrestaurant, name, address, image, verification FROM restaurant WHERE (approval = 0 OR approval IS NULL) ORDER BY idrestaurant";
$stmt = $conn->prepare($query);
$stmt->execute();
$pendingRestaurants = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Pending Merchant Approvals</title>
    <?php include '../inc/head.inc.php'; ?>
</head>

<body>
    <main>
        <div class="container-fluid">
            <div class="row flex-nowrap">
                <!-- Sidebar Column -->
                <div class="col-auto px-sm-2 px-0 bg-dark">
                    <?php include '../inc/nav_admin.inc.php'; ?>
                </div>

                <!-- Main Content Column -->
                <div class="col py-3">
                    <h1>Pending Merchant Approvals</h1>
                    <?php if ($pendingRestaurants->num_rows > 0): ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Restaurant ID</th>
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>Logo/Image</th>
                                    <th>Verification File</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($restaurant = $pendingRestaurants->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $restaurant['idrestaurant'] ?></td>
                                        <td><?= htmlspecialchars($restaurant['name']) ?></td>
                                        <td><?= htmlspecialchars($restaurant['address']) ?></td>
                                        <td>
                                            <?php if (!empty($restaurant['image'])): ?>
                                                <img src="<?= htmlspecialchars($restaurant['image']) ?>" alt="Restaurant Logo" style="width:100px;">
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($restaurant['verification'])): ?>
                                                <a href="/admin/view_verification.php?file=<?= urlencode($restaurant['verification']) ?>" target="_blank">View Verification</a>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-success" onclick="approveMerchant(<?= $restaurant['idrestaurant'] ?>)">Approve</button>
                                            <button class="btn btn-danger" onclick="rejectMerchant(<?= $restaurant['idrestaurant'] ?>)">Reject</button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No pending approvals at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script>
            function approveMerchant(id) {
                if (confirm("Are you sure you want to approve this merchant?")) {
                    const params = new URLSearchParams();
                    params.append('id', id);
                    params.append('action', 'approve');

                    fetch('/admin/requests/process_approve_merchant.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: params.toString()
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert("Merchant approved.");
                                location.reload();
                            } else {
                                alert("Failed to approve merchant: " + data.message);
                            }
                        })
                        .catch(error => console.error("Error:", error));
                }
            }

            function rejectMerchant(id) {
                if (confirm("Are you sure you want to reject this merchant?")) {
                    const params = new URLSearchParams();
                    params.append('id', id);
                    params.append('action', 'reject');

                    fetch('/admin/requests/process_reject_merchant.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: params.toString()
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert("Merchant rejected.");
                                location.reload();
                            } else {
                                alert("Failed to reject merchant: " + data.message);
                            }
                        })
                        .catch(error => console.error("Error:", error));
                }
            }
        </script>
    </main>
</body>

</html>