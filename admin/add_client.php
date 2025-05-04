<?php
include "../config/database.php";
include "../admin_auth.php";

$session = check_admin_auth();
$admin = isset($session["name"]) ? $session["name"] : 'Admin';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $phone = $_POST["mobile"];
    $email = $_POST["email"];
    $address = $_POST["address"];

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO clients (name, phone, email, address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $phone, $email, $address);

    if ($stmt->execute()) {
        echo "<script>alert('Client Added Successfully'); window.location='add_order.php';</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Client - Tailor Stitch</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php">
            <i class="ri-dashboard-line"></i>
            Dashboard
        </a>
        <a href="add_client.php" class="active">
            <i class="ri-user-add-line"></i>
            Add Client
        </a>
        <a href="view_clients.php">
            <i class="ri-team-line"></i>
            View Clients
        </a>
        <a href="add_order.php">
            <i class="ri-file-add-line"></i>
            Add Order
        </a>
        <a href="view_orders.php">
            <i class="ri-file-list-3-line"></i>
            View Orders
        </a>
        <a href="manage_staff.php">
            <i class="ri-user-settings-line"></i>
            Manage Staff
        </a>
        <a href="../logout.php">
            <i class="ri-logout-box-line"></i>
            Logout
        </a>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h3>Add New Client</h3>
            <div class="flex items-center gap-4">
                <a href="view_clients.php" class="btn btn-secondary">
                    <i class="ri-team-line"></i>
                    View All Clients
                </a>
            </div>
        </div>

        <div class="dashboard-box">
            <div class="form-container">
                <form name="clientForm" method="POST" onsubmit="return validateForm()" class="client-form">
                    <div class="grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" name="name" id="name" class="form-control" 
                                   placeholder="Enter client's full name" required>
                        </div>

                        <div class="form-group">
                            <label for="mobile">Phone Number</label>
                            <input type="tel" name="mobile" id="mobile" class="form-control" 
                                   placeholder="Enter phone number" required
                                   pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number">
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" name="email" id="email" class="form-control" 
                                   placeholder="Enter email address" required>
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea name="address" id="address" class="form-control" 
                                      placeholder="Enter complete address" required
                                      rows="3"></textarea>
                        </div>
                    </div>

                    <div class="form-actions mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-user-add-line"></i>
                            Add Client
                        </button>
                        <a href="view_clients.php" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--gray-700);
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.5rem;
            background: white;
            color: var(--gray-800);
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
    </style>

    <script>
        function validateForm() {
            var name = document.forms["clientForm"]["name"].value;
            var mobile = document.forms["clientForm"]["mobile"].value;
            var email = document.forms["clientForm"]["email"].value;
            var address = document.forms["clientForm"]["address"].value;

            if (name.trim() === "" || mobile.trim() === "" || email.trim() === "" || address.trim() === "") {
                alert("All fields must be filled out");
                return false;
            }

            // Validate phone number format
            var phonePattern = /^[0-9]{10}$/;
            if (!phonePattern.test(mobile)) {
                alert("Please enter a valid 10-digit phone number");
                return false;
            }

            // Validate email format
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                alert("Please enter a valid email address");
                return false;
            }

            return true;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const currentLocation = window.location.pathname;
            const sidebarLinks = document.querySelectorAll('.sidebar a');
            
            sidebarLinks.forEach(link => {
                if (link.getAttribute('href') === currentLocation.split('/').pop()) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
