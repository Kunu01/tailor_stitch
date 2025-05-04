<?php
include "../config/database.php";
include "../admin_auth.php";

// Check admin authentication
$session = check_admin_auth();

// Get client ID from URL
$client_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($client_id > 0) {
    // First delete related orders
    $delete_orders = "DELETE FROM orders WHERE client_id = $client_id";
    $conn->query($delete_orders);
    
    // Then delete the client
    $delete_query = "DELETE FROM clients WHERE id = $client_id";
    if ($conn->query($delete_query)) {
        header("Location: view_clients.php?success=Client deleted successfully");
    } else {
        header("Location: view_clients.php?error=Failed to delete client");
    }
} else {
    header("Location: view_clients.php?error=Invalid client ID");
}
exit;
?>
