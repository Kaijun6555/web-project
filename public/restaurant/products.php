<?php
require '../../db/db-connect.php';
session_start();
if ($_SESSION['restaurant_id']) {
    $restaurant_id = $_SESSION['restaurant_id'];
} else {
    header('Location: /restaurant/restaurant_login.php');
}

$stmt = $conn->prepare("SELECT idmenu_item, itemName, price, availability, description, image FROM menu_item WHERE restaurant_id = ? ORDER BY itemName");
$stmt->bind_param("i", $restaurant_id);
$stmt->execute();
$menu_result = $stmt->get_result();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['ItemName'], $_POST['description'], $_POST['price'], $_POST['availability'])) {
        $itemName = sanitize_input($_POST['ItemName']);
        $description = sanitize_input($_POST['description']);
        $availability = $_POST['availability'];
        $price = $_POST['price'];
        $image_file_tmp_path = $_FILES['image']['tmp_name'];
        $image_file_name = $_FILES['image']['name'];
        $image_file_size = $_FILES['image']['size'];
        $target_directory = "uploads/";
        $target_file_path = $target_directory . basename($image_file_name);
        $image_file_type = strtolower(pathinfo($target_file_path, PATHINFO_EXTENSION));
        $check_image_file = getimagesize($image_file_tmp_path);
        if ($check_image_file === false) {
            echo "File is not an image";
            exit();
        }
        if ($image_file_size > 300000) {
            echo "Sorry, your file is too large";
            exit();
        }
        if (
            $image_file_type != "jpg" && $image_file_type != "png" && $image_file_type != "jpeg"
            && $image_file_type != "gif"
        ) {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            exit();
        }

        if (!is_dir($target_directory)) {
            mkdir($target_directory, 0777, true);
        }
        if (is_dir($target_directory)) {
            chmod($target_directory, 0775);
        }
        if (!move_uploaded_file($image_file_tmp_path, $target_file_path)) {
            echo "Sorry, there was an error uploading your file.";
            exit();
        }

        try {
            $stmt = $conn->prepare("INSERT INTO menu_item (restaurant_id, itemName, description, availability, price, image) VALUES (?, ?, ?, ?, ?, ?)");

            $stmt->bind_param("isssds", $restaurant_id, $itemName, $description, $availability, $price, $target_file_path);
            $stmt->execute();
            $stmt->close();
            echo "
            <script>
                alert('Please fill in all fields!');
                window.location.href = '/restaurant/products.php';
            </script>
        ";
            header("Location: /restaurant/products.php");

            exit();
        } catch (PDOException $e) {
            echo "Error:" . $e->getMessage();
        }
    } else {
        echo "
            <script>
                alert('Please fill in all fields!');
                window.location.href = '/restaurant/products.php';
            </script>
        ";
        header("Location: /restaurant/products.php");
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Food</title>
    <?php include '../inc/head.inc.php'; ?>
</head>

<body>

    <div class="container-fluid">
        <div class="row flex-nowrap">
            <?php include '../inc/nav_restaurant.inc.php'; ?>

            <div class="col py-3">
                <!-- Button to trigger Modal -->
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                    Add Product
                </button>

                <!-- Modal -->
                <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Modal Title</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="productName">Item Name:</label>
                                        <input type="text" class="form-control" id="ItemName" name="ItemName">
                                    </div>
                                    <div class="form-group">
                                        <label for="description">Description:</label>
                                        <input type="text" class="form-control" id="description" name="description">
                                    </div>
                                    <div class="form-group">
                                        <label for="availability">Availability</label>
                                        <select type="text" class="form-control" id="availability" name="availability">
                                            <option value="yes">Yes</option>
                                            <option value="no">No</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="price">Purchase Price:</label>
                                        <input type="number" step=".01" class="form-control" id="price" name="price">
                                    </div>
                                    <div class="form-group">
                                        <label for="image">Select Image of food to upload:(Only .JPEG, .JPG, .PNG, .GIFS Allowed)</label>
                                        <input type="file" class="form-control" id="image" name="image">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Add Menu Item</button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>

</body>

</html>