<!DOCTYPE html>
<html lang="en">
<?php
include "inc/head.inc.php";
?>

<body>
    <?php
    include "inc/nav.inc.php";
    ?>
    <main class="container">
        <h1>Member Login</h1>
        <p>
            New Member? Please go to the
            <a href="#">Member Registration page</a>.
        </p>
        <form action="process_login.php" method="post" novalidate>

           
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input required maxlength="45" class="form-control" type="email" id="email" name="email" placeholder="Enter email">
            </div>

            <div class="mb-3">
                <label for="pwd" class="form-label">Password:</label>
                <input required class="form-control" type="password" id="pwd" name="pwd" placeholder="Enter password">
            </div>

            <div class="mb-3">
                <button class="btn btn-primary"type="submit">Submit</button>
            </div>
        </form>
    </main>
    <?php
    include "../inc/footer.inc.php";
    ?>
</body>

</html>