<?php
include "../config/database.php";
include "../staff_auth.php";

$session = check_staff_auth();
$staff = isset($session["name"]) ? $session["name"] : "Guest";
$staff_id = $session["user_id"];

$orders_today = $conn->query("SELECT COUNT(*) AS count FROM orders WHERE staff_id = $staff_id AND status = 'Successful' AND DATE(IFNULL(updated_at, '1970-01-01')) = CURDATE()")->fetch_assoc()["count"];
$orders_month = $conn->query("SELECT COUNT(*) AS count FROM orders WHERE staff_id = $staff_id AND status = 'Successful' AND MONTH(IFNULL(updated_at, '1970-01-01')) = MONTH(CURDATE())")->fetch_assoc()["count"];
$pending_orders = $conn->query("SELECT COUNT(*) AS count FROM orders WHERE staff_id = $staff_id AND status = 'Pending'")->fetch_assoc()["count"];
$total_orders = $conn->query("SELECT COUNT(*) AS count FROM orders WHERE staff_id = $staff_id")->fetch_assoc()["count"];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Staff Dashboard - Tailor Stitch</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <h2>Staff Panel</h2>
        <a href="dashboard.php" class="active">
            <i class="ri-dashboard-line"></i>
            Dashboard
        </a>
        <a href="orders.php">
            <i class="ri-file-list-3-line"></i>
            View Orders
        </a>
        <a href="../logout.php">
            <i class="ri-logout-box-line"></i>
            Logout
        </a>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h3>Welcome, <?php echo htmlspecialchars($staff); ?></h3>
            <div class="flex items-center gap-4">
                <i class="ri-notification-3-line"></i>
                <div class="flex items-center gap-2">
                    <i class="ri-user-line"></i>
                    <span><?php echo htmlspecialchars($staff); ?></span>
                </div>
            </div>
        </div>

        <div class="grid-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem;">
            <div class="dashboard-box">
                <div class="flex items-center justify-between mb-4">
                    <h3>Today's Completed</h3>
                    <i class="ri-check-double-line" style="font-size: 1.5rem; color: var(--success);"></i>
                </div>
                <p><?php echo $orders_today; ?></p>
            </div>

            <div class="dashboard-box">
                <div class="flex items-center justify-between mb-4">
                    <h3>Monthly Completed</h3>
                    <i class="ri-calendar-check-line" style="font-size: 1.5rem; color: var(--primary);"></i>
                </div>
                <p><?php echo $orders_month; ?></p>
            </div>

            <div class="dashboard-box">
                <div class="flex items-center justify-between mb-4">
                    <h3>Pending Orders</h3>
                    <i class="ri-time-line" style="font-size: 1.5rem; color: var(--warning);"></i>
                </div>
                <p><?php echo $pending_orders; ?></p>
            </div>

            <div class="dashboard-box">
                <div class="flex items-center justify-between mb-4">
                    <h3>Total Orders</h3>
                    <i class="ri-stack-line" style="font-size: 1.5rem; color: var(--secondary);"></i>
                </div>
                <p><?php echo $total_orders; ?></p>
            </div>
        </div>

        <div class="mt-4">
            <div class="dashboard-box">
                <h3 class="mb-4">Quick Actions</h3>
                <div class="grid-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <a href="orders.php" class="btn btn-primary">
                        <i class="ri-file-list-3-line"></i>
                        View Orders
                    </a>
                    <a href="orders.php?filter=pending" class="btn btn-warning">
                        <i class="ri-time-line"></i>
                        Pending Orders
                    </a>
                    <a href="orders.php?filter=completed" class="btn btn-success">
                        <i class="ri-check-double-line"></i>
                        Completed Orders
                    </a>
                </div>
            </div>
        </div>

        <?php if ($pending_orders > 0): ?>
        <div class="mt-4">
            <div class="dashboard-box">
                <div class="flex items-center justify-between mb-4">
                    <h3>Reminder</h3>
                    <i class="ri-alarm-warning-line" style="font-size: 1.5rem; color: var(--warning);"></i>
                </div>
                <p>You have <?php echo $pending_orders; ?> pending orders that need attention.</p>
                <a href="orders.php?filter=pending" class="btn btn-warning mt-4">
                    View Pending Orders
                </a>
            </div>
        </div>
        <?php endif; ?>
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
