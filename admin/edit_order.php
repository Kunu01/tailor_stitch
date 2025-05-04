<?php
include "../config/database.php";
include "../admin_auth.php";

$session = check_admin_auth();
$admin = isset($session["name"]) ? $session["name"] : 'Admin';

$order_id = $_GET['id'] ?? null;

if (!$order_id) {
    header("Location: view_orders.php");
    exit();
}

$order = $conn->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();
$client = $conn->query("SELECT * FROM clients WHERE id = {$order['client_id']}")->fetch_assoc();
$measurement = $conn->query("SELECT * FROM measurements WHERE client_id = {$client['id']}")->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_id = $_POST["client_id"];
    $cloth_type = $_POST["cloth_type"];
    $cloth_material = $_POST["cloth_material"];

    // Measurement values
    $neck = $_POST["neck"] ?? 0;
    $shoulder = $_POST["shoulder"] ?? 0;
    $length = $_POST["length"] ?? 0;
    $chest = $_POST["chest"] ?? 0;
    $arm = $_POST["arm"] ?? 0;
    $wrist = $_POST["wrist"] ?? 0;
    $forearm = $_POST["forearm"] ?? 0;
    $pant_length = $_POST["pant_length"] ?? 0;
    $waist = $_POST["waist"] ?? 0;
    $hip = $_POST["hip"] ?? 0;
    $thighs = $_POST["thighs"] ?? 0;
    $calfs = $_POST["calfs"] ?? 0;

    // Update measurements using prepared statement
    $stmt = $conn->prepare("UPDATE measurements SET neck=?, shoulder=?, length=?, chest=?, arm=?, wrist=?, forearm=?, 
                           pant_length=?, waist=?, hip=?, thighs=?, calfs=? WHERE client_id=?");
    $stmt->bind_param("ddddddddddddi", $neck, $shoulder, $length, $chest, $arm, $wrist, $forearm, 
                      $pant_length, $waist, $hip, $thighs, $calfs, $client_id);
    $stmt->execute();

    // Update order using prepared statement
    $stmt = $conn->prepare("UPDATE orders SET cloth_material=?, cloth_type=? WHERE id=?");
    $stmt->bind_param("ssi", $cloth_material, $cloth_type, $order_id);
    $stmt->execute();

    echo "<script>alert('Order updated successfully!'); window.location='view_orders.php';</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Order - Tailor Stitch</title>
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
        <a href="view_orders.php" class="active">
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
            <div class="flex items-center gap-2">
                <a href="view_orders.php" class="btn btn-secondary">
                    <i class="ri-arrow-left-line"></i>
                    Back to Orders
                </a>
                <h3>Edit Order #<?php echo str_pad($order_id, 4, '0', STR_PAD_LEFT); ?></h3>
            </div>
        </div>

        <div class="dashboard-box">
            <div class="form-container">
                <div class="client-info mb-4">
                    <h4 class="text-gray-600 mb-2">Client Information</h4>
                    <div class="flex items-center gap-2">
                        <i class="ri-user-line"></i>
                        <span class="font-semibold"><?php echo htmlspecialchars($client['name']); ?></span>
                        <span class="text-gray-500">(<?php echo htmlspecialchars($client['phone']); ?>)</span>
                    </div>
                </div>

                <form name="orderForm" method="POST" action="" class="order-form">
                    <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                    
                    <div class="grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                        <div class="form-group">
                            <label for="cloth_material">Cloth Material</label>
                            <select name="cloth_material" id="cloth_material" class="form-control">
                                <option value="Cotton" <?php echo ($order['cloth_material'] == "Cotton") ? 'selected' : ''; ?>>Cotton</option>
                                <option value="Nylon" <?php echo ($order['cloth_material'] == "Nylon") ? 'selected' : ''; ?>>Nylon</option>
                                <option value="Silk" <?php echo ($order['cloth_material'] == "Silk") ? 'selected' : ''; ?>>Silk</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="cloth_type">Cloth Type</label>
                            <select name="cloth_type" id="cloth_type" class="form-control" onchange="toggleMeasurements()">
                                <option value="Shirt" <?php echo ($order['cloth_type'] == "Shirt") ? 'selected' : ''; ?>>Shirt</option>
                                <option value="Pant" <?php echo ($order['cloth_type'] == "Pant") ? 'selected' : ''; ?>>Pant</option>
                                <option value="Both" <?php echo ($order['cloth_type'] == "Both") ? 'selected' : ''; ?>>Both</option>
                            </select>
                        </div>
                    </div>

                    <div id="shirtMeasurements" class="measurement-section" 
                         style="display: <?php echo ($order['cloth_type'] == "Shirt" || $order['cloth_type'] == "Both") ? 'block' : 'none'; ?>;">
                        <h4 class="text-primary mb-4">Shirt Measurements</h4>
                        <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                            <div class="measurement-item">
                                <label for="neck">Neck (inches)</label>
                                <input type="number" step="0.1" name="neck" class="form-control" 
                                       value="<?php echo htmlspecialchars($measurement['neck']); ?>">
                            </div>
                            <div class="measurement-item">
                                <label for="shoulder">Shoulder (inches)</label>
                                <input type="number" step="0.1" name="shoulder" class="form-control" 
                                       value="<?php echo htmlspecialchars($measurement['shoulder']); ?>">
                            </div>
                            <div class="measurement-item">
                                <label for="length">Length (inches)</label>
                                <input type="number" step="0.1" name="length" class="form-control" 
                                       value="<?php echo htmlspecialchars($measurement['length']); ?>">
                            </div>
                            <div class="measurement-item">
                                <label for="chest">Chest (inches)</label>
                                <input type="number" step="0.1" name="chest" class="form-control" 
                                       value="<?php echo htmlspecialchars($measurement['chest']); ?>">
                            </div>
                            <div class="measurement-item">
                                <label for="arm">Arm (inches)</label>
                                <input type="number" step="0.1" name="arm" class="form-control" 
                                       value="<?php echo htmlspecialchars($measurement['arm']); ?>">
                            </div>
                            <div class="measurement-item">
                                <label for="wrist">Wrist (inches)</label>
                                <input type="number" step="0.1" name="wrist" class="form-control" 
                                       value="<?php echo htmlspecialchars($measurement['wrist']); ?>">
                            </div>
                            <div class="measurement-item">
                                <label for="forearm">Forearm (inches)</label>
                                <input type="number" step="0.1" name="forearm" class="form-control" 
                                       value="<?php echo htmlspecialchars($measurement['forearm']); ?>">
                            </div>
                        </div>
                    </div>

                    <div id="pantMeasurements" class="measurement-section" 
                         style="display: <?php echo ($order['cloth_type'] == "Pant" || $order['cloth_type'] == "Both") ? 'block' : 'none'; ?>;">
                        <h4 class="text-primary mb-4">Pant Measurements</h4>
                        <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                            <div class="measurement-item">
                                <label for="pant_length">Length (inches)</label>
                                <input type="number" step="0.1" name="pant_length" class="form-control" 
                                       value="<?php echo htmlspecialchars($measurement['pant_length']); ?>">
                            </div>
                            <div class="measurement-item">
                                <label for="waist">Waist (inches)</label>
                                <input type="number" step="0.1" name="waist" class="form-control" 
                                       value="<?php echo htmlspecialchars($measurement['waist']); ?>">
                            </div>
                            <div class="measurement-item">
                                <label for="hip">Hip (inches)</label>
                                <input type="number" step="0.1" name="hip" class="form-control" 
                                       value="<?php echo htmlspecialchars($measurement['hip']); ?>">
                            </div>
                            <div class="measurement-item">
                                <label for="thighs">Thighs (inches)</label>
                                <input type="number" step="0.1" name="thighs" class="form-control" 
                                       value="<?php echo htmlspecialchars($measurement['thighs']); ?>">
                            </div>
                            <div class="measurement-item">
                                <label for="calfs">Calfs (inches)</label>
                                <input type="number" step="0.1" name="calfs" class="form-control" 
                                       value="<?php echo htmlspecialchars($measurement['calfs']); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line"></i>
                            Update Order
                        </button>
                        <a href="view_orders.php" class="btn btn-secondary">
                            <i class="ri-close-line"></i>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .measurement-section {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid var(--gray-200);
        }
        .measurement-item {
            background: var(--gray-50);
            padding: 1rem;
            border-radius: 0.5rem;
        }
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        .text-gray-500 {
            color: var(--gray-500);
        }
        .text-gray-600 {
            color: var(--gray-600);
        }
        .font-semibold {
            font-weight: 600;
        }
    </style>

    <script>
        function toggleMeasurements() {
            var cloth_type = document.getElementById("cloth_type").value;
            document.getElementById("shirtMeasurements").style.display = 
                (cloth_type === "Shirt" || cloth_type === "Both") ? "block" : "none";
            document.getElementById("pantMeasurements").style.display = 
                (cloth_type === "Pant" || cloth_type === "Both") ? "block" : "none";
        }

        document.addEventListener('DOMContentLoaded', function() {
            const currentLocation = window.location.pathname;
            const sidebarLinks = document.querySelectorAll('.sidebar a');
            
            sidebarLinks.forEach(link => {
                if (link.getAttribute('href') === 'view_orders.php') {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
