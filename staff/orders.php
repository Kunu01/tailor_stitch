<?php
include "../config/database.php";
include "../staff_auth.php";

$session = check_staff_auth();
$staff_id = $session["user_id"];

$filter = $_GET['filter'] ?? '';
$where_clause = "";

switch ($filter) {
    case 'pending':
        $where_clause = "WHERE o.status = 'Pending'";
        break;
    case 'completed':
        $where_clause = "WHERE o.status = 'Successful'";
        break;
    default:
        $where_clause = "WHERE o.status = 'Pending' OR o.status = 'New'";
}

$orders = $conn->query("SELECT o.*, c.name AS client_name 
                       FROM orders o 
                       JOIN clients c ON o.client_id = c.id 
                       $where_clause 
                       ORDER BY o.created_at DESC");

// Accept order
if (isset($_GET["accept"])) {
    $order_id = $_GET["accept"];
    $stmt = $conn->prepare("UPDATE orders SET staff_id = ?, status = 'Pending' WHERE id = ? AND status = 'New'");
    $stmt->bind_param("ii", $staff_id, $order_id);
    $stmt->execute();
    $stmt->close();
    header("Location: order_detail.php?order_id=$order_id");
    exit();
}

// Mark order as successful
if (isset($_GET["complete"])) {
    $order_id = $_GET["complete"];
    $stmt = $conn->prepare("UPDATE orders SET status = 'Successful', updated_at = NOW() WHERE id = ? AND staff_id = ?");
    $stmt->bind_param("ii", $order_id, $staff_id);
    $stmt->execute();
    $stmt->close();

    // Fetch order details
    $order = $conn->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();
    $client = $conn->query("SELECT * FROM clients WHERE id = {$order['client_id']}")->fetch_assoc();

    // Send email notification
    $to = $client["email"];
    $subject = "Your Order is Ready - Tailor Stitch";
    $message = "Dear " . $client["name"] . ",\n\nYour order is completed.\nDetails:\n - Order Type: " . ($order["cloth_type"] ?? "N/A") . "\n - Material: " . ($order["cloth_material"] ?? "N/A") . "\n\nThank you for choosing Tailor Stitch.";
    $headers = "From: no-reply@tailorstitch.com";

    if (!mail($to, $subject, $message, $headers)) {
        error_log("Email failed to send to $to");
    }

    header("Location: orders.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Orders - Tailor Stitch</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <h2>Staff Panel</h2>
        <a href="dashboard.php">
            <i class="ri-dashboard-line"></i>
            Dashboard
        </a>
        <a href="orders.php" class="active">
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
            <h3>Orders Management</h3>
            <div class="flex items-center gap-4">
                <div class="flex gap-2">
                    <a href="?filter=" class="btn <?php echo $filter == '' ? 'btn-primary' : 'btn-secondary'; ?>">All</a>
                    <a href="?filter=pending" class="btn <?php echo $filter == 'pending' ? 'btn-primary' : 'btn-secondary'; ?>">Pending</a>
                    <a href="?filter=completed" class="btn <?php echo $filter == 'completed' ? 'btn-primary' : 'btn-secondary'; ?>">Completed</a>
                </div>
            </div>
        </div>

        <div class="dashboard-box">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Order Type</th>
                            <th>Material</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $orders->fetch_assoc()) { ?>
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <i class="ri-user-line"></i>
                                        <?php echo htmlspecialchars($order["client_name"]); ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($order["cloth_type"]); ?></td>
                                <td><?php echo htmlspecialchars($order["cloth_material"]); ?></td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($order["status"]); ?>">
                                        <?php echo htmlspecialchars($order["status"]); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($order["created_at"])); ?></td>
                                <td>
                                    <?php if ($order["status"] == "Pending" && $order["staff_id"] == $staff_id) { ?>
                                        <a href="order_complete.php?order_id=<?php echo $order["id"]; ?>" class="btn btn-success btn-sm">
                                            <i class="ri-check-line"></i> Complete
                                        </a>
                                    <?php } elseif ($order["status"] == "New") { ?>
                                        <a href="?accept=<?php echo $order["id"]; ?>" class="btn btn-primary btn-sm">
                                            <i class="ri-add-line"></i> Accept
                                        </a>
                                    <?php } else { ?>
                                        <span class="text-gray-400">No Action</span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .status-badge.new {
            background-color: #e0f2fe;
            color: #0369a1;
        }
        .status-badge.pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-badge.successful {
            background-color: #dcfce7;
            color: #166534;
        }
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const currentLocation = window.location.pathname;
            const sidebarLinks = document.querySelectorAll('.sidebar a');
            
            sidebarLinks.forEach(link => {
                if (link.getAttribute('href') === currentLocation.split('/').pop()) {
                    link.classList.add('active');
                }
            });

            // Function to refresh the orders table
            function refreshOrdersTable() {
                const tableContainer = document.querySelector('.table-container');
                const currentFilter = new URLSearchParams(window.location.search).get('filter') || '';
                
                fetch('get_orders_table.php?filter=' + currentFilter)
                    .then(response => response.text())
                    .then(html => {
                        tableContainer.innerHTML = html;
                    })
                    .catch(error => console.error('Error:', error));
            }

            // Refresh table every 30 seconds
            setInterval(refreshOrdersTable, 30000);

            // Initial refresh
            refreshOrdersTable();
        });
    </script>
</body>
</html>
