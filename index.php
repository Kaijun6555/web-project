<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Food</title>
        <?php include 'inc/head.inc.php'; ?>
    </head>
    <body>
        <?php include 'inc/nav.inc.php'; ?>         
        <?php include 'inc/header.inc.php'; ?>
        <main class="container">
            <h2>Restaurants</h2>
            <div class="row">
                <?php
                require 'db-connect.php';
                $stmt = $conn->prepare("SELECT id, name, description FROM restaurants ORDER BY name");
                $stmt->execute();
                $result = $stmt->get_result();
                $stmt->close();
                
                while ($row = $result->fetch_assoc()):
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($row['description']) ?></p>
                            <a href="restaurant.php?id=<?= $row['id'] ?>" class="btn btn-primary">View Menu</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </main>
        <?php include 'inc/footer.inc.php'; ?>            
    </body>
</html>