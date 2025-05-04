<?php
include "../config/database.php";
include "../admin_auth.php";

$session = check_admin_auth();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: manage_staff.php");
    exit();
}

// Validate input
$id = $_POST['id'] ?? '';
$name = trim($_POST['name'] ?? '');
$dob = trim($_POST['dob'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');
$specialization = trim($_POST['specialization'] ?? '');
$staff_id = trim($_POST['staff_id'] ?? '');

$errors = [];

// Validate required fields
if (empty($name) || empty($dob) || empty($phone) || empty($email) || empty($address) || empty($specialization) || empty($staff_id)) {
    $errors[] = "All fields are required.";
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
}

// Validate phone number (10 digits)
if (!preg_match('/^[0-9]{10}$/', $phone)) {
    $errors[] = "Phone number must be 10 digits.";
}

// Validate specialization
$valid_specializations = ['Shirt', 'Pant', 'Both'];
if (!in_array($specialization, $valid_specializations)) {
    $errors[] = "Invalid specialization selected.";
}

// Validate date format
if (!strtotime($dob)) {
    $errors[] = "Invalid date format.";
}

// Check if staff_id already exists (excluding current staff)
$check_sql = "SELECT id FROM staff WHERE staff_id = ? AND id != ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("si", $staff_id, $id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    $errors[] = "Staff ID already exists.";
}

// Check if email already exists (excluding current staff)
$check_sql = "SELECT id FROM staff WHERE email = ? AND id != ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("si", $email, $id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    $errors[] = "Email already exists.";
}

if (empty($errors)) {
    // Update staff information
    $sql = "UPDATE staff SET name=?, dob=?, phone=?, email=?, address=?, specialization=?, staff_id=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $name, $dob, $phone, $email, $address, $specialization, $staff_id, $id);

    if ($stmt->execute()) {
        // Log the update
        $admin_id = $session["user_id"];
        $log_sql = "INSERT INTO activity_log (admin_id, action, details) VALUES (?, 'update_staff', ?)";
        $log_stmt = $conn->prepare($log_sql);
        $log_details = "Updated staff ID: $staff_id";
        $log_stmt->bind_param("is", $admin_id, $log_details);
        $log_stmt->execute();

        echo "<script>
            alert('Staff information updated successfully!');
            window.location='manage_staff.php';
        </script>";
    } else {
        echo "<script>
            alert('Error updating staff information: " . $stmt->error . "');
            window.location='edit_staff.php?id=$id';
        </script>";
    }
} else {
    $error_message = implode("\\n", $errors);
    echo "<script>
        alert('Please correct the following errors:\\n$error_message');
        window.location='edit_staff.php?id=$id';
    </script>";
}
?>
