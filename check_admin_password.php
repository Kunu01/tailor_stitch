<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function writeLog($message) {
    file_put_contents('admin_check.log', $message . "\n", FILE_APPEND);
}

include "config/database.php";
writeLog("Database connection established");

// First check if admin table exists
$tables = $conn->query("SHOW TABLES LIKE 'admin'");
if ($tables->num_rows === 0) {
    writeLog("Error: 'admin' table does not exist in the database");
    die();
}
writeLog("Admin table exists");

// Check table structure
$structure = $conn->query("DESCRIBE admin");
writeLog("\nTable structure:");
while ($row = $structure->fetch_assoc()) {
    writeLog($row['Field'] . " - " . $row['Type']);
}

// Try to get admin data
$stmt = $conn->prepare("SELECT id, username, password FROM admin");
if (!$stmt) {
    writeLog("Prepare failed: " . $conn->error);
    die();
}

$stmt->execute();
$result = $stmt->get_result();

writeLog("\nAdmin Records:");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        writeLog("Admin ID: " . $row["id"]);
        writeLog("Username: " . $row["username"]);
        writeLog("Password hash length: " . strlen($row["password"]));
        writeLog("Password starts with \$2y\$: " . (strpos($row["password"], '$2y$') === 0 ? 'Yes' : 'No'));
        writeLog("-------------------");
    }
} else {
    writeLog("No admin records found");
}

$stmt->close();
$conn->close();
?>
