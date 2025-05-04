<?php
include "../config/database.php";
include "../admin_auth.php";

$session = check_admin_auth();
$admin = isset($session["name"]) ? $session["name"] : 'Admin';

$total_clients = $conn->query("SELECT COUNT(*) AS count FROM clients")->fetch_assoc()["count"];
$total_orders = $conn->query("SELECT COUNT(*) AS count FROM orders WHERE MONTH(created_at) = MONTH(CURDATE())")->fetch_assoc()["count"];
$total_staff = $conn->query("SELECT COUNT(*) AS count FROM staff")->fetch_assoc()["count"];
$today_orders = $conn->query("SELECT COUNT(*) AS count FROM orders WHERE DATE(created_at) = CURDATE()")->fetch_assoc()["count"];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Tailor Stitch</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php" class="active">
            <i class="ri-dashboard-line"></i>
            Dashboard
        </a>
        <a href="add_client.php">
            <i class="ri-user-add-line"></i>
            Add Client
        </a>
        <a href="view_clients.php">
            <i class="ri-team-line"></i>
            View Clients
        </a>
        <a href="add_order.php">
            <i class="ri-file-add-line"></i>
            Add Order
        </a>
        <a href="view_orders.php">
            <i class="ri-file-list-3-line"></i>
            View Orders
        </a>
        <a href="manage_staff.php">
            <i class="ri-user-settings-line"></i>
            Manage Staff
        </a>
        <a href="../logout.php">
            <i class="ri-logout-box-line"></i>
            Logout
        </a>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h3>Welcome, <?php echo htmlspecialchars($admin); ?></h3>
            <div class="flex items-center gap-4">
                <i class="ri-notification-3-line"></i>
                <div class="flex items-center gap-2">
                    <i class="ri-user-line"></i>
                    <span><?php echo htmlspecialchars($admin); ?></span>
                </div>
            </div>
        </div>

        <div class="grid-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem;">
            <div class="dashboard-box">
                <div class="flex items-center justify-between mb-4">
                    <h3>Today's Orders</h3>
                    <i class="ri-shopping-bag-line" style="font-size: 1.5rem; color: var(--primary);"></i>
                </div>
                <p><?php echo $today_orders; ?></p>
            </div>

            <div class="dashboard-box">
                <div class="flex items-center justify-between mb-4">
                    <h3>Total Clients</h3>
                    <i class="ri-user-line" style="font-size: 1.5rem; color: var(--success);"></i>
                </div>
                <p><?php echo $total_clients; ?></p>
            </div>

            <div class="dashboard-box">
                <div class="flex items-center justify-between mb-4">
                    <h3>Monthly Orders</h3>
                    <i class="ri-calendar-line" style="font-size: 1.5rem; color: var(--warning);"></i>
                </div>
                <p><?php echo $total_orders; ?></p>
            </div>

            <div class="dashboard-box">
                <div class="flex items-center justify-between mb-4">
                    <h3>Total Staff</h3>
                    <i class="ri-team-line" style="font-size: 1.5rem; color: var(--secondary);"></i>
                </div>
                <p><?php echo $total_staff; ?></p>
            </div>
        </div>

        <div class="mt-4">
            <div class="dashboard-box">
                <h3 class="mb-4">Quick Actions</h3>
                <div class="grid-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <a href="add_order.php" class="btn btn-primary">
                        <i class="ri-add-line"></i>
                        New Order
                    </a>
                    <a href="add_client.php" class="btn btn-secondary">
                        <i class="ri-user-add-line"></i>
                        New Client
                    </a>
                    <a href="manage_staff.php" class="btn btn-success">
                        <i class="ri-user-settings-line"></i>
                        Manage Staff
                    </a>
                    <a href="view_orders.php" class="btn btn-secondary">
                        <i class="ri-file-list-3-line"></i>
                        View Orders
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add active class to current sidebar link
        document.addEventListener('DOMContentLoaded', function() {
            const currentLocation = window.location.pathname;
            const sidebarLinks = document.querySelectorAll('.sidebar a');
            
            sidebarLinks.forEach(link => {
                if (link.getAttribute('href') === currentLocation.split('/').pop()) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
