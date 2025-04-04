<?php
require '../../db/db-connect.php';
session_start();
if ($_SESSION['restaurant_id']) {
    $restaurant_id = $_SESSION['restaurant_id'];
} else {
    header('Location: /restaurant/restaurant_login.php');
}
function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
$stmt = $conn->prepare("SELECT idmenu_item, itemName, price, availability, description, image FROM menu_item WHERE restaurant_id = ? ORDER BY itemName");
$stmt->bind_param("i", $restaurant_id);
$stmt->execute();
$menu_result = $stmt->get_result();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_idmenu_item']) && !empty($_POST['delete_idmenu_item']) && isset($_POST['confirm_delete']) && !empty($_POST['confirm_delete'])) {
        $delete_idmenu_item = $_POST['delete_idmenu_item'];

        // Prepare and execute the delete query
        $stmt = $conn->prepare("DELETE FROM menu_item WHERE idmenu_item = ?");
        $stmt->bind_param("i", $delete_idmenu_item);
        $stmt->execute();
        $stmt->close();

        // Redirect back to products page after deletion
        header("Location: /restaurant/products.php");
        exit();
    } elseif (isset($_POST['idmenu_item']) && !empty($_POST['idmenu_item'])) {
        if (isset($_POST['update-ItemName'], $_POST['update-description'], $_POST['update-price'], $_POST['update-availability'])) {
            $idmenu_item = $_POST['idmenu_item'];
            $itemName = sanitize_input($_POST['update-ItemName']);
            $description = sanitize_input($_POST['update-description']);
            $availability = $_POST['update-availability'];
            $price = $_POST['update-price'];
            $image = $_POST['update-image'];

            // Prepare the update query
            $stmt = $conn->prepare("UPDATE menu_item SET itemName = ?, description = ?, availability = ?, price = ?, image = ? WHERE idmenu_item = ?");
            $stmt->bind_param("sssdsd", $itemName, $description, $availability, $price, $image, $idmenu_item);
            $stmt->execute();
            $stmt->close();

            // Redirect to the product page after update
            header("Location: /restaurant/products.php");
            exit();
        }
    } else {
        if (isset($_POST['ItemName'], $_POST['description'], $_POST['price'], $_POST['availability'])) {
            $itemName = sanitize_input($_POST['ItemName']);
            $description = sanitize_input($_POST['description']);
            $availability = $_POST['availability'];
            $price = $_POST['price'];
            $image = $_POST['image'];
            $image_data = getimagesize($image);
            $target_directory = "uploads/";
            if (!strpos($image_data['mime'], 'image/') === 0) {
                header("Location: /restaurant/products.php");
                echo "
            <script>
                alert('Please fill in all fields!');
                window.location.href = '/restaurant/products.php';
            </script>
            ";
            }
            if (is_dir($target_directory)) {
                unlink($target_directory);
            }

            $stmt = $conn->prepare("INSERT INTO menu_item (restaurant_id, itemName, description, availability, price, image) VALUES (?, ?, ?, ?, ?, ?)");

            $stmt->bind_param("isssds", $restaurant_id, $itemName, $description, $availability, $price, $image);
            $stmt->execute();
            $menuitem_id = $stmt->insert_id;
            $stmt->close();
            header("Location: /restaurant/products.php");
        } else {
            header("Location: /restaurant/products.php");
            echo "
            <script>
                alert('Please fill in all fields!');
                window.location.href = '/restaurant/products.php';
            </script>
        ";
        }
    }
}
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Select all 'Edit Product' buttons
        const editButtons = document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#UpdateProductModal"]');

        // Loop through each button and add the event listener
        editButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                // Get the data attributes from the button that was clicked
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-update-name');
                const description = button.getAttribute('data-update-description');
                const price = button.getAttribute('data-update-price');
                const availability = button.getAttribute('data-update-availability');
                const image = button.getAttribute('data-update-image');

                // Populate the modal fields
                document.getElementById('idmenu_item').value = id; // Set the hidden ID field
                document.getElementById('update-ItemName').value = name; // Set the Item Name field
                document.getElementById('update-description').value = description; // Set the Description field
                document.getElementById('update-price').value = price; // Set the Price field
                document.getElementById('update-availability').value = availability; // Set the Availability field
                document.getElementById('update-image').value = image; // Set the Image URL field
            });
        });
        const deleteButtons = document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#DeleteProductModal"]');

        // Loop through each button and add the event listener
        deleteButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                // Get the data-id attribute from the button that was clicked
                const id = button.getAttribute('data-delete-id');
                const confirm_delete = button.getAttribute('data-confirm-delete');
                // Set the value of the hidden input field in the Delete Modal
                document.getElementById('delete_idmenu_item').value = id;
                document.getElementById('confirm_delete').value = confirm_delete;
            });
        });
    });
</script>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Food</title>
    <?php include '../inc/head.inc.php'; ?>
</head>

<body>
    <main>
        <div class="container-fluid">
            <div class="row flex-nowrap">
                <?php include '../inc/nav_restaurant.inc.php'; ?>

                <div class="col py-3">
                    <!-- Button to trigger Modal -->
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#AddProductModal">
                        Add Product
                    </button>

                    <!-- Modal for adding product -->
                    <div class="modal fade" id="AddProductModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Add Item</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="ItemtName">Item Name:</label>
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
                                            <label for="image">Input link to Image of food</label>
                                            <input type="text" class="form-control" id="image" name="image">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Add Menu Item</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- Modal for update product -->
                    <div class="modal fade" id="UpdateProductModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Edit product</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="update-ItemName">Item Name:</label>
                                            <input type="text" class="form-control" id="update-ItemName" name="update-ItemName">
                                        </div>
                                        <div class="form-group">
                                            <label for="update-description">Description:</label>
                                            <input type="text" class="form-control" id="update-description" name="update-description">
                                        </div>
                                        <div class="form-group">
                                            <label for="update-availability">Availability</label>
                                            <select type="text" class="form-control" id="update-availability" name="update-availability">
                                                <option value="yes">Yes</option>
                                                <option value="no">No</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="update-price">Purchase Price:</label>
                                            <input type="number" step=".01" class="form-control" id="update-price" name="update-price">
                                        </div>
                                        <div class="form-group">
                                            <label for="update-image">Input link to Image of food</label>
                                            <input type="text" class="form-control" id="update-image" name="update-image">
                                        </div>
                                        <input type="hidden" name="idmenu_item" id="idmenu_item">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Save New changes</button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                    <!-- Displaying Products as Cards -->
                    <div class="row mt-4">
                        <?php if ($menu_result->num_rows > 0): ?>
                            <?php while ($row = $menu_result->fetch_assoc()) : ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card">
                                        <img src="<?php echo htmlspecialchars($row['image']); ?>" class="card-img-top" alt="Product Image">
                                        <div class="card-body">
                                            <h1 class="card-title"><?php echo htmlspecialchars($row['itemName']); ?></h1>
                                            <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>
                                            <p class="card-text"><strong>Price:</strong> $<?php echo number_format($row['price'], 2); ?></p>
                                            <p class="card-text"><strong>Availability:</strong> <?php echo htmlspecialchars($row['availability']); ?></p>
                                            <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#UpdateProductModal"
                                                data-id="<?php echo $row['idmenu_item']; ?>"
                                                data-update-name="<?php echo htmlspecialchars($row['itemName']); ?>"
                                                data-update-description="<?php echo htmlspecialchars($row['description']); ?>"
                                                data-update-price="<?php echo $row['price']; ?>"
                                                data-update-availability="<?php echo $row['availability']; ?>"
                                                data-update-image="<?php echo htmlspecialchars($row['image']); ?>">
                                                Edit Product
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <h1>No menu items available.</h1>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>