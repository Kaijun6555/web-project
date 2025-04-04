<?php
session_start();
require '../../db/db-connect.php';

// Redirect to admin login if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /user/login.php");
    exit();
}

// Fetch count of pending restaurants
$query = "SELECT COUNT(*) as pendingCount FROM restaurant WHERE (approval = 0 OR approval IS NULL)";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$pendingCount = $data['pendingCount'] ?? 0;
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Admin Dashboard</title>
    <?php include '../inc/head.inc.php'; ?>
</head>

<body>
    <main>
        <div class="container-fluid">
            <div class="row flex-nowrap">

                <div class="col-auto px-sm-2 px-0 bg-dark">
                    <?php include '../inc/nav_admin.inc.php'; ?>
                </div>

                <!-- Main content area -->
                <div class="col py-3">
                    <h1>Welcome to the Admin Dashboard</h1>

                    <div class="row">
                        <!-- Card for Pending Merchant Approvals -->
                        <div class="col-md-4">
                            <div class="card text-white bg-primary mb-3">
                                <div class="card-body">
                                    <h2 class="card-title">Pending Merchant Approvals</h2>
                                    <p class="card-text"><?= $pendingCount ?> merchant(s) pending approval.</p>
                                    <a href="/admin/merchants_pending.php" class="btn btn-light">View Pending</a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </main>
</body>

</html>