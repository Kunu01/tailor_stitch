<?php
include "../config/database.php";
include "../admin_auth.php";

$session = check_admin_auth();
$admin = isset($session["name"]) ? $session["name"] : 'Admin';

$filter = $_GET['filter'] ?? '';
$where_clause = "";

switch ($filter) {
    case 'pending':
        $where_clause = "WHERE o.status = 'Pending'";
        break;
    case 'completed':
        $where_clause = "WHERE o.status = 'Successful'";
        break;
    case 'new':
        $where_clause = "WHERE o.status = 'New'";
        break;
}

$orders = $conn->query("SELECT o.*, c.name AS client_name, 
                       COALESCE(s.name, o.staff_name) AS staff_name 
                       FROM orders o 
                       JOIN clients c ON o.client_id = c.id 
                       LEFT JOIN staff s ON o.staff_id = s.id
                       $where_clause
                       ORDER BY o.created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Orders - Tailor Stitch</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php">
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
        <a href="view_orders.php" class="active">
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
            <h3>Orders Management</h3>
            <div class="flex items-center gap-4">
                <div class="flex gap-2">
                    <a href="?filter=" class="btn <?php echo $filter == '' ? 'btn-primary' : 'btn-secondary'; ?>">
                        <i class="ri-list-check"></i> All
                    </a>
                    <a href="?filter=new" class="btn <?php echo $filter == 'new' ? 'btn-primary' : 'btn-secondary'; ?>">
                        <i class="ri-file-add-line"></i> New
                    </a>
                    <a href="?filter=pending" class="btn <?php echo $filter == 'pending' ? 'btn-primary' : 'btn-secondary'; ?>">
                        <i class="ri-time-line"></i> Pending
                    </a>
                    <a href="?filter=completed" class="btn <?php echo $filter == 'completed' ? 'btn-primary' : 'btn-secondary'; ?>">
                        <i class="ri-check-double-line"></i> Completed
                    </a>
                </div>
                <a href="add_order.php" class="btn btn-primary">
                    <i class="ri-add-line"></i>
                    New Order
                </a>
            </div>
        </div>

        <div class="dashboard-box">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Client</th>
                            <th>Type</th>
                            <th>Material</th>
                            <th>Status</th>
                            <th>Staff</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $orders->fetch_assoc()) { ?>
                            <tr>
                                <td>#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></td>
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
                                <td>
                                    <?php if ($order["staff_name"]) { ?>
                                        <div class="flex items-center gap-2">
                                            <i class="ri-user-star-line"></i>
                                            <?php echo htmlspecialchars($order["staff_name"]); ?>
                                        </div>
                                    <?php } else { ?>
                                        <span class="text-gray-400">Not Assigned</span>
                                    <?php } ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($order["created_at"])); ?></td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <?php if ($order["status"] === "New") { ?>
                                            <a href="edit_order.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary btn-sm">
                                                <i class="ri-edit-line"></i>
                                            </a>
                                            <a href="delete_order.php?id=<?php echo $order['id']; ?>" 
                                               onclick="return confirm('Are you sure you want to delete this order?');" 
                                               class="btn btn-danger btn-sm">
                                                <i class="ri-delete-bin-line"></i>
                                            </a>
                                        <?php } ?>
                                    </div>
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
            padding: 0.25rem 0.75rem;
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
        .text-gray-400 {
            color: var(--gray-400);
        }
    </style>

    <script>
        function refreshOrdersTable() {
            const filter = new URLSearchParams(window.location.search).get('filter') || '';
            fetch(`get_orders_table.php?filter=${filter}`)
                .then(response => response.text())
                .then(html => {
                    document.querySelector('.table-container').innerHTML = html;
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Refresh table every 30 seconds
            setInterval(refreshOrdersTable, 30000);

            // Update active sidebar link
            const currentLocation = window.location.pathname;
            const sidebarLinks = document.querySelectorAll('.sidebar a');
            
            sidebarLinks.forEach(link => {
                if (link.getAttribute('href') === currentLocation.split('/').pop()) {
                    link.classList.add('active');
                }
            });

            // Add click handlers to filter buttons
            document.querySelectorAll('.flex.gap-2 a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = new URL(this.href);
                    const filter = url.searchParams.get('filter') || '';
                    history.pushState(null, '', this.href);
                    refreshOrdersTable();
                    
                    // Update active button state
                    document.querySelectorAll('.flex.gap-2 a').forEach(btn => {
                        btn.classList.remove('btn-primary');
                        btn.classList.add('btn-secondary');
                    });
                    this.classList.remove('btn-secondary');
                    this.classList.add('btn-primary');
                });
            });
        })
    </script>
</body>
</html>
