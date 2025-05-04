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
?>
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