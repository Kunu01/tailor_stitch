<?php
include "../config/database.php";
include "../admin_auth.php";

$session = check_admin_auth();
$admin = isset($session["name"]) ? $session["name"] : 'Admin';

// Add Staff
if (isset($_POST['add_staff'])) {
    $name = trim($_POST['name']);
    $dob = trim($_POST['dob']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $specialization = trim($_POST['specialization']);
    $staff_id = trim($_POST['staff_id']);
    $password = trim($_POST['password']);

    // Input validation
    if (empty($name) || empty($dob) || empty($phone) || empty($email) || empty($address) || empty($specialization) || empty($staff_id) || empty($password)) {
        echo "<script>alert('All fields are required!');</script>";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format!');</script>";
        exit;
    }

    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        echo "<script>alert('Invalid phone number!');</script>";
        exit;
    }

    $valid_specializations = ['Shirt', 'Pant', 'Both'];
    if (!in_array($specialization, $valid_specializations)) {
        echo "<script>alert('Invalid specialization!');</script>";
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check for duplicate staff ID or email
    $check_sql = "SELECT * FROM staff WHERE staff_id = ? OR email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $staff_id, $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "<script>alert('Staff ID or email already exists!');</script>";
    } else {
        $sql = "INSERT INTO staff (name, dob, phone, email, address, specialization, staff_id, password) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $name, $dob, $phone, $email, $address, $specialization, $staff_id, $hashed_password);
        
        if ($stmt->execute()) {
            echo "<script>alert('Staff added successfully!'); window.location='manage_staff.php';</script>";
        } else {
            echo "<script>alert('Error adding staff: " . $stmt->error . "');</script>";
        }
    }
}

// Delete Staff
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($conn->query("DELETE FROM staff WHERE id=$id") === TRUE) {
        echo "<script>alert('Staff deleted successfully!'); window.location='manage_staff.php';</script>";
    } else {
        echo "<script>alert('Error deleting staff: " . $conn->error . "');</script>";
    }
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$where_clause = $search ? "WHERE name LIKE '%$search%' OR email LIKE '%$search%' OR staff_id LIKE '%$search%'" : "";

$result = $conn->query("SELECT * FROM staff $where_clause ORDER BY name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Staff - Tailor Stitch</title>
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
        <a href="add_client.php">
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
        <a href="manage_staff.php" class="active">
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
            <h3>Staff Management</h3>
            <div class="flex items-center gap-4">
                <form action="" method="GET" class="search-form">
                    <div class="flex items-center gap-2">
                        <div class="search-input">
                            <i class="ri-search-line"></i>
                            <input type="text" name="search" placeholder="Search staff..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <button type="submit" class="btn btn-secondary">Search</button>
                    </div>
                </form>
                <button onclick="toggleForm()" class="btn btn-primary">
                    <i class="ri-user-add-line"></i>
                    Add New Staff
                </button>
            </div>
        </div>

        <div id="addStaffForm" class="dashboard-box" style="display: none;">
            <div class="form-container">
                <h3 class="mb-4">Add New Staff</h3>
                <form action="manage_staff.php" method="POST">
                    <div class="grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" name="name" id="name" required>
                        </div>

                        <div class="form-group">
                            <label for="dob">Date of Birth</label>
                            <input type="date" name="dob" id="dob" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="text" name="phone" id="phone" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" name="email" id="email" required>
                        </div>

                        <div class="form-group">
                            <label for="staff_id">Staff ID</label>
                            <input type="text" name="staff_id" id="staff_id" required>
                        </div>

                        <div class="form-group">
                            <label for="specialization">Specialization</label>
                            <select name="specialization" id="specialization" required>
                                <option value="">Select Specialization</option>
                                <option value="Shirt">Shirt</option>
                                <option value="Pant">Pant</option>
                                <option value="Both">Both</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" name="password" id="password" required>
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" name="address" id="address" required>
                        </div>
                    </div>

                    <div class="flex gap-2 mt-4">
                        <button type="submit" name="add_staff" class="btn btn-primary">
                            <i class="ri-user-add-line"></i>
                            Add Staff
                        </button>
                        <button type="button" onclick="toggleForm()" class="btn btn-secondary">
                            <i class="ri-close-line"></i>
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="dashboard-box mt-4">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Staff ID</th>
                            <th>Name</th>
                            <th>Contact Info</th>
                            <th>Specialization</th>
                            <th>Orders</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { 
                            // Get order count for this staff
                            $order_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE staff_id = {$row['id']}")->fetch_assoc()['count'];
                        ?>
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <i class="ri-user-line"></i>
                                        <?php echo htmlspecialchars($row['staff_id']); ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td>
                                    <div class="contact-info">
                                        <div class="flex items-center gap-2">
                                            <i class="ri-mail-line"></i>
                                            <?php echo htmlspecialchars($row['email']); ?>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <i class="ri-phone-line"></i>
                                            <?php echo htmlspecialchars($row['phone']); ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="specialization-badge <?php echo strtolower($row['specialization']); ?>">
                                        <?php echo htmlspecialchars($row['specialization']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="order-count">
                                        <?php echo $order_count; ?> orders
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <a href="edit_staff.php?id=<?php echo $row['id']; ?>" 
                                           class="btn btn-secondary btn-sm" title="Edit Staff">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <a href="delete_staff.php?id=<?php echo $row['id']; ?>" 
                                           onclick="return confirm('Are you sure you want to delete this staff member?');"
                                           class="btn btn-danger btn-sm" title="Delete Staff">
                                            <i class="ri-delete-bin-line"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .search-form {
            flex: 1;
        }
        .search-input {
            position: relative;
            width: 300px;
        }
        .search-input i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
        }
        .search-input input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid var(--gray-200);
            border-radius: 0.5rem;
            background: white;
        }
        .contact-info > div {
            margin-bottom: 0.25rem;
        }
        .specialization-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .specialization-badge.shirt {
            background-color: #e0f2fe;
            color: #0369a1;
        }
        .specialization-badge.pant {
            background-color: #fef3c7;
            color: #92400e;
        }
        .specialization-badge.both {
            background-color: #dcfce7;
            color: #166534;
        }
        .order-count {
            background: var(--gray-100);
            color: var(--gray-700);
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
        }
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
    </style>

    <script>
        function toggleForm() {
            var form = document.getElementById("addStaffForm");
            form.style.display = form.style.display === "none" ? "block" : "none";
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
