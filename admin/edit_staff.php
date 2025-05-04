<?php
include "../config/database.php";
include "../admin_auth.php";

$session = check_admin_auth();
$admin = isset($session["name"]) ? $session["name"] : 'Admin';

// Check if ID is provided
if (!isset($_GET['id'])) {
    echo "<script>alert('No staff ID provided!'); window.location='manage_staff.php';</script>";
    exit;
}

$id = $_GET['id'];

// Fetch staff details
$sql = "SELECT * FROM staff WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('No staff found with this ID!'); window.location='manage_staff.php';</script>";
    exit;
}

$staff = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Staff - Tailor Stitch</title>
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
            <div class="flex items-center gap-2">
                <a href="manage_staff.php" class="btn btn-secondary">
                    <i class="ri-arrow-left-line"></i>
                    Back to Staff List
                </a>
                <h3>Edit Staff Details</h3>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <i class="ri-user-line"></i>
                    <span><?php echo htmlspecialchars($admin); ?></span>
                </div>
            </div>
        </div>

        <div class="dashboard-box">
            <div class="form-container">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-xl font-semibold">Staff ID: <?php echo htmlspecialchars($staff['staff_id']); ?></h4>
                        <p class="text-gray-500">Update staff information below</p>
                    </div>
                    <span class="specialization-badge <?php echo strtolower($staff['specialization']); ?>">
                        <?php echo htmlspecialchars($staff['specialization']); ?>
                    </span>
                </div>

                <form action="update_staff.php" method="POST" class="update-form">
                    <input type="hidden" name="id" value="<?php echo $staff['id']; ?>">
                    
                    <div class="grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" name="name" id="name" class="form-control" 
                                   value="<?php echo htmlspecialchars($staff['name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="staff_id">Staff ID</label>
                            <input type="text" name="staff_id" id="staff_id" class="form-control" 
                                   value="<?php echo htmlspecialchars($staff['staff_id']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="dob">Date of Birth</label>
                            <input type="date" name="dob" id="dob" class="form-control" 
                                   value="<?php echo htmlspecialchars($staff['dob']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" name="phone" id="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($staff['phone']); ?>" required
                                   pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number">
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" name="email" id="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($staff['email']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="specialization">Specialization</label>
                            <select name="specialization" id="specialization" class="form-control" required>
                                <option value="Shirt" <?php echo ($staff['specialization'] == 'Shirt') ? 'selected' : ''; ?>>Shirt</option>
                                <option value="Pant" <?php echo ($staff['specialization'] == 'Pant') ? 'selected' : ''; ?>>Pant</option>
                                <option value="Both" <?php echo ($staff['specialization'] == 'Both') ? 'selected' : ''; ?>>Both</option>
                            </select>
                        </div>

                        <div class="form-group" style="grid-column: span 2;">
                            <label for="address">Address</label>
                            <textarea name="address" id="address" class="form-control" rows="3" required><?php echo htmlspecialchars($staff['address']); ?></textarea>
                        </div>
                    </div>

                    <div class="form-actions mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line"></i>
                            Update Staff
                        </button>
                        <a href="manage_staff.php" class="btn btn-secondary">
                            <i class="ri-close-line"></i>
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
        .text-xl {
            font-size: 1.25rem;
            line-height: 1.75rem;
        }
        .font-semibold {
            font-weight: 600;
        }
        .text-gray-500 {
            color: var(--gray-500);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const currentLocation = window.location.pathname;
            const sidebarLinks = document.querySelectorAll('.sidebar a');
            
            sidebarLinks.forEach(link => {
                if (link.getAttribute('href') === 'manage_staff.php') {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
