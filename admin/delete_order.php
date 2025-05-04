<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

$order_id = $_GET['id'] ?? null;

if (!$order_id) {
    header("Location: view_orders.php");
    exit();
}

// Fetch the order to get the client ID
$order = $conn->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();
$client_id = $order['client_id'];

// Delete the order
$conn->query("DELETE FROM orders WHERE id = $order_id");

// Optionally, delete the associated measurements if no other orders exist for the client
$remaining_orders = $conn->query("SELECT COUNT(*) AS count FROM orders WHERE client_id = $client_id")->fetch_assoc()['count'];
if ($remaining_orders == 0) {
    $conn->query("DELETE FROM measurements WHERE client_id = $client_id");
}

echo "<script>alert('Order deleted successfully!'); window.location='view_orders.php';</script>";
?>