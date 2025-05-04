<?php
include "../config/database.php";
include "../admin_auth.php";

// Check admin authentication
$session = check_admin_auth();

// Get staff ID from URL
$staff_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($staff_id > 0) {
    // First, get the staff member's name and other details
    $staff_query = "SELECT name, staff_id, specialization FROM staff WHERE id = $staff_id";
    $staff_result = $conn->query($staff_query);
    
    if ($staff = $staff_result->fetch_assoc()) {
        // Update orders to store staff name directly
        $update_orders = "UPDATE orders SET 
                         staff_name = '{$staff['name']}',
                         staff_id = NULL 
                         WHERE staff_id = $staff_id";
        $conn->query($update_orders);
        
        // Now delete the staff member
        $delete_query = "DELETE FROM staff WHERE id = $staff_id";
        if ($conn->query($delete_query)) {
            header("Location: manage_staff.php?success=Staff deleted successfully");
        } else {
            header("Location: manage_staff.php?error=Failed to delete staff");
        }
    } else {
        header("Location: manage_staff.php?error=Staff not found");
    }
} else {
    header("Location: manage_staff.php?error=Invalid staff ID");
}
exit;
?>
