<?php
include "../config/database.php";

$sql = "ALTER TABLE orders ADD COLUMN staff_name VARCHAR(255) DEFAULT NULL AFTER staff_id";

if ($conn->query($sql)) {
    echo "Staff name column added successfully";
} else {
    echo "Error adding staff name column: " . $conn->error;
}
?>
