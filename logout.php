<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Store the role before clearing session
$was_admin = isset($_SESSION["role"]) && $_SESSION["role"] === "admin";

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to login page with appropriate message
$redirect = "login.php?logged_out=1";
if ($was_admin) {
    $redirect .= "&from=admin";
} else {
    $redirect .= "&from=staff";
}

header("Location: $redirect");
exit();
?>
