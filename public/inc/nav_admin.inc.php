<div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 bg-dark">
    <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100">
        <a href="/admin/dashboard.php" class="d-flex align-items-center pb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <span class="fs-5 d-none d-sm-inline">ADMIN PANEL</span>
        </a>
        <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start" id="menu">
            <li class="nav-item">
                <a href="/admin/dashboard.php" class="nav-link align-middle px-0">
                    <i class="fs-4 bi-house"></i>
                    <span class="ms-1 d-none d-sm-inline">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/merchants_pending.php" class="nav-link align-middle px-0">
                    <i class="fs-4 bi-person-check"></i>
                    <span class="ms-1 d-none d-sm-inline">Pending Merchants</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/settings.php" class="nav-link align-middle px-0">
                    <i class="fs-4 bi-gear"></i>
                    <span class="ms-1 d-none d-sm-inline">Settings</span>
                </a>
            </li>
        </ul>
        <hr>
        <div class="dropdown pb-4">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                id="dropdownAdmin" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="https://github.com/mdo.png" alt="Admin Avatar" width="30" height="30" class="rounded-circle">
                <span class="d-none d-sm-inline mx-1">Admin ID: <?php echo isset($_SESSION['admin_id']) ? htmlspecialchars($_SESSION['admin_id']) : 'Unknown'; ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownAdmin">
                <li><a class="dropdown-item" href="/logout.php">Sign out</a></li>
            </ul>
        </div>
    </div>
</div>
