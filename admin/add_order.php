<?php
include "../config/database.php";
include "../admin_auth.php";

$session = check_admin_auth();
$admin = isset($session["name"]) ? $session["name"] : 'Admin';

// Fetch clients
$clients = $conn->query("SELECT * FROM clients");

$selected_client_id = "";
$selected_cloth_material = "";
$selected_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_id = $_POST["client_id"];
    $cloth_type = isset($_POST["cloth_type"]) ? $_POST["cloth_type"] : null;
    $cloth_material = isset($_POST["cloth_material"]) ? $_POST["cloth_material"] : null;

    // Measurement values (default 0 for unused)
    $neck = $shoulder = $length = $chest = $arm = $wrist = $forearm = 0;
    $pant_length = $waist = $hip = $thighs = $calfs = 0;

    if ($cloth_type == "Shirt" || $cloth_type == "Both") {
        $neck = $_POST["neck"];
        $shoulder = $_POST["shoulder"];
        $length = $_POST["length"];
        $chest = $_POST["chest"];
        $arm = $_POST["arm"];
        $wrist = $_POST["wrist"];
        $forearm = $_POST["forearm"];
    }

    if ($cloth_type == "Pant" || $cloth_type == "Both") {
        $pant_length = $_POST["pant_length"];
        $waist = $_POST["waist"];
        $hip = $_POST["hip"];
        $thighs = $_POST["thighs"];
        $calfs = $_POST["calfs"];
    }

    $conn->query("INSERT INTO measurements (client_id, neck, shoulder, length, chest, arm, wrist, forearm, pant_length, waist, hip, thighs, calfs) 
                  VALUES ('$client_id', '$neck', '$shoulder', '$length', '$chest', '$arm', '$wrist', '$forearm', '$pant_length', '$waist', '$hip', '$thighs', '$calfs')");

    if ($cloth_material) {
        $conn->query("INSERT INTO orders (client_id, cloth_material, cloth_type) VALUES ('$client_id', '$cloth_material', '$cloth_type')");
    }

    echo "<script>alert('Order added successfully!'); window.location='view_orders.php';</script>";
} else {
    $selected_client_id = isset($_POST["client_id"]) ? $_POST["client_id"] : "";
    $selected_cloth_material = isset($_POST["cloth_material"]) ? $_POST["cloth_material"] : "";
    $selected_type = isset($_POST["cloth_type"]) ? $_POST["cloth_type"] : "";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Order - Tailor Stitch</title>
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
        <a href="add_order.php" class="active">
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
            <h3>Add New Order</h3>
            <div class="flex items-center gap-4">
                <i class="ri-notification-3-line"></i>
                <div class="flex items-center gap-2">
                    <i class="ri-user-line"></i>
                    <span><?php echo htmlspecialchars($admin); ?></span>
                </div>
            </div>
        </div>

        <div class="dashboard-box">
            <form name="orderForm" method="POST" action="" onsubmit="return validateForm()" class="form-container">
                <div class="form-group">
                    <label for="client_id">Select Client</label>
                    <select name="client_id" id="client_id" class="form-control">
                        <option value="">Select Client</option>
                        <?php while ($client = $clients->fetch_assoc()) { ?>
                            <option value="<?php echo $client["id"]; ?>" <?php echo ($client["id"] == $selected_client_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($client["name"]); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="cloth_material">Cloth Material</label>
                    <select name="cloth_material" id="cloth_material" class="form-control">
                        <option value="">Select Material</option>
                        <option value="Cotton" <?php echo ($selected_cloth_material == "Cotton") ? 'selected' : ''; ?>>Cotton</option>
                        <option value="Nylon" <?php echo ($selected_cloth_material == "Nylon") ? 'selected' : ''; ?>>Nylon</option>
                        <option value="Silk" <?php echo ($selected_cloth_material == "Silk") ? 'selected' : ''; ?>>Silk</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="cloth_type">Cloth Type</label>
                    <select name="cloth_type" id="cloth_type" onchange="toggleMeasurements()" class="form-control">
                        <option value="">Select Type</option>
                        <option value="Shirt" <?php echo ($selected_type == "Shirt") ? 'selected' : ''; ?>>Shirt</option>
                        <option value="Pant" <?php echo ($selected_type == "Pant") ? 'selected' : ''; ?>>Pant</option>
                        <option value="Both" <?php echo ($selected_type == "Both") ? 'selected' : ''; ?>>Both</option>
                    </select>
                </div>

                <div id="shirtMeasurements" class="measurement-section" style="display:none;">
                    <h4 class="text-primary mb-4">Shirt Measurements</h4>
                    <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div class="form-group">
                            <label for="neck">Neck (inches)</label>
                            <input type="number" step="0.1" name="neck" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="shoulder">Shoulder (inches)</label>
                            <input type="number" step="0.1" name="shoulder" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="length">Length (inches)</label>
                            <input type="number" step="0.1" name="length" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="chest">Chest (inches)</label>
                            <input type="number" step="0.1" name="chest" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="arm">Arm (inches)</label>
                            <input type="number" step="0.1" name="arm" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="wrist">Wrist (inches)</label>
                            <input type="number" step="0.1" name="wrist" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="forearm">Forearm (inches)</label>
                            <input type="number" step="0.1" name="forearm" class="form-control">
                        </div>
                    </div>
                </div>

                <div id="pantMeasurements" class="measurement-section" style="display:none;">
                    <h4 class="text-primary mb-4">Pant Measurements</h4>
                    <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div class="form-group">
                            <label for="pant_length">Length (inches)</label>
                            <input type="number" step="0.1" name="pant_length" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="waist">Waist (inches)</label>
                            <input type="number" step="0.1" name="waist" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="hip">Hip (inches)</label>
                            <input type="number" step="0.1" name="hip" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="thighs">Thighs (inches)</label>
                            <input type="number" step="0.1" name="thighs" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="calfs">Calfs (inches)</label>
                            <input type="number" step="0.1" name="calfs" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-add-line"></i>
                        Add Order
                    </button>
                    <a href="view_orders.php" class="btn btn-secondary">
                        <i class="ri-arrow-left-line"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleMeasurements() {
            var cloth_type = document.getElementById("cloth_type").value;
            document.getElementById("shirtMeasurements").style.display = (cloth_type === "Shirt" || cloth_type === "Both") ? "block" : "none";
            document.getElementById("pantMeasurements").style.display = (cloth_type === "Pant" || cloth_type === "Both") ? "block" : "none";
        }

        function validateForm() {
            var client_id = document.forms["orderForm"]["client_id"].value;
            var cloth_material = document.forms["orderForm"]["cloth_material"].value;
            var cloth_type = document.forms["orderForm"]["cloth_type"].value;

            if (client_id == "" || cloth_material == "" || cloth_type == "") {
                alert("Please fill in all required fields");
                return false;
            }

            if (cloth_type === "Shirt" || cloth_type === "Both") {
                var shirtFields = ["neck", "shoulder", "length", "chest", "arm", "wrist", "forearm"];
                for (var i = 0; i < shirtFields.length; i++) {
                    var value = document.forms["orderForm"][shirtFields[i]].value;
                    if (value == "" || value <= 0) {
                        alert("Please enter valid measurements for all shirt fields");
                        return false;
                    }
                }
            }

            if (cloth_type === "Pant" || cloth_type === "Both") {
                var pantFields = ["pant_length", "waist", "hip", "thighs", "calfs"];
                for (var i = 0; i < pantFields.length; i++) {
                    var value = document.forms["orderForm"][pantFields[i]].value;
                    if (value == "" || value <= 0) {
                        alert("Please enter valid measurements for all pant fields");
                        return false;
                    }
                }
            }

            return true;
        }

        // Add active class to current sidebar link
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
