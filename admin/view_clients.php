<?php
include "../config/database.php";
include "../admin_auth.php";

$session = check_admin_auth();
$admin = isset($session["name"]) ? $session["name"] : 'Admin';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$where_clause = $search ? "WHERE name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%'" : "";

$clients = $conn->query("SELECT * FROM clients $where_clause ORDER BY name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Clients - Tailor Stitch</title>
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
        <a href="view_clients.php" class="active">
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
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>

        <div class="top-bar">
            <h3>Clients Management</h3>
            <div class="flex items-center gap-4">
                <form action="" method="GET" class="search-form">
                    <div class="flex items-center gap-2">
                        <div class="search-input">
                            <i class="ri-search-line"></i>
                            <input type="text" name="search" placeholder="Search clients..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <button type="submit" class="btn btn-secondary">Search</button>
                    </div>
                </form>
                <a href="add_client.php" class="btn btn-primary">
                    <i class="ri-user-add-line"></i>
                    Add New Client
                </a>
            </div>
        </div>

        <div class="dashboard-box">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Client ID</th>
                            <th>Name</th>
                            <th>Contact Info</th>
                            <th>Address</th>
                            <th>Orders</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($client = $clients->fetch_assoc()) { 
                            // Get order count for this client
                            $order_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE client_id = {$client['id']}")->fetch_assoc()['count'];
                        ?>
                            <tr>
                                <td>#<?php echo str_pad($client['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <i class="ri-user-line"></i>
                                        <?php echo htmlspecialchars($client["name"]); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="contact-info">
                                        <div class="flex items-center gap-2">
                                            <i class="ri-mail-line"></i>
                                            <?php echo htmlspecialchars($client["email"]); ?>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <i class="ri-phone-line"></i>
                                            <?php echo htmlspecialchars($client["phone"]); ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <i class="ri-map-pin-line"></i>
                                        <?php echo htmlspecialchars($client["address"]); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="order-count">
                                        <?php echo $order_count; ?> orders
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <a href="add_order.php?client_id=<?php echo $client['id']; ?>" 
                                           class="btn btn-primary btn-sm" title="New Order">
                                            <i class="ri-add-line"></i>
                                        </a>
                                        <a href="edit_client.php?id=<?php echo $client['id']; ?>" 
                                           class="btn btn-secondary btn-sm" title="Edit Client">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <a href="delete_client.php?id=<?php echo $client['id']; ?>" 
                                           onclick="return confirm('Are you sure you want to delete this client?');"
                                           class="btn btn-danger btn-sm" title="Delete Client">
                                            <i class="ri-delete-bin-line"></i>
                                        </a>
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
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
        }
        .alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .alert-success {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .search-form {
            flex: 1;
        }
        .search-input {
            position: relative;
            width: 300px;
        }
        .search-input i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
        }
        .search-input input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid var(--gray-200);
            border-radius: 0.5rem;
            background: white;
        }
        .search-input input:focus {
            border-color: var(--primary);
            outline: none;
        }
        .contact-info > div {
            margin-bottom: 0.25rem;
        }
        .contact-info > div:last-child {
            margin-bottom: 0;
        }
        .order-count {
            background: var(--gray-100);
            color: var(--gray-700);
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
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
        });
    </script>
</body>
</html>
