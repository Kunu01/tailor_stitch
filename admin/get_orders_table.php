<?php
include "../config/database.php";
include "../admin_auth.php";

$session = check_admin_auth();

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