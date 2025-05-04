<?php
// Use a unique session name for staff
session_name('staff_session');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function set_staff_session($user_id, $name) {
    $_SESSION["user_id"] = $user_id;
    $_SESSION["name"] = $name;
    $_SESSION["role"] = "staff";
    $_SESSION["staff_login_time"] = time();
}

function check_staff_auth() {
    // Check if user is logged in
    if (!isset($_SESSION["user_id"]) || !isset($_SESSION["role"])) {
        header("Location: ../login.php?type=staff");
        exit();
    }

    // Check if user is a staff member
    if ($_SESSION["role"] !== "staff") {
        header("Location: ../login.php?type=staff");
        exit();
    }

    // Check session timeout (2 hours)
    $timeout = 7200; // 2 hours in seconds
    if (isset($_SESSION["staff_login_time"]) && (time() - $_SESSION["staff_login_time"] > $timeout)) {
        // Session has expired
        session_unset();
        session_destroy();
        header("Location: ../login.php?timeout=1&type=staff");
        exit();
    }

    // Update last activity time
    $_SESSION["staff_login_time"] = time();

    return [
        "user_id" => $_SESSION["user_id"],
        "name" => $_SESSION["name"],
        "role" => $_SESSION["role"]
    ];
}
?>
