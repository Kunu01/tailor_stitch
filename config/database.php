<?php
$host = "localhost";
$username = "root";  // Change if using another username
$password = "";  // Change if using a password
$database = "tailor_stitch";

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
