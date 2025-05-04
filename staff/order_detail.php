<?php
include "../config/database.php";
include "../staff_auth.php";

$session = check_staff_auth();
$staff_id = $session["user_id"];

$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    header("Location: orders.php");
    exit();
}

$order = $conn->query("SELECT o.*, c.name AS client_name, c.email AS client_email 
                       FROM orders o 
                       JOIN clients c ON o.client_id = c.id 
                       WHERE o.id = $order_id")->fetch_assoc();
$measurements = $conn->query("SELECT * FROM measurements WHERE client_id = {$order['client_id']}")->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $send_email = isset($_POST['send_email']) && $_POST['send_email'] == '1';
    
    $stmt = $conn->prepare("UPDATE orders SET status = 'Successful', updated_at = NOW() WHERE id = ? AND staff_id = ?");
    $stmt->bind_param("ii", $order_id, $staff_id);
    $stmt->execute();
    $stmt->close();

    if ($send_email) {
        // Send email notification
        $to = $order["client_email"];
        $subject = "Your Order is Ready - Tailor Stitch";
        $message = "Dear " . $order["client_name"] . ",\n\nYour order is completed.\nDetails:\n - Order Type: " . ($order["cloth_type"] ?? "N/A") . "\n - Material: " . ($order["cloth_material"] ?? "N/A") . "\n\nThank you for choosing Tailor Stitch.";
        $headers = "From: no-reply@tailorstitch.com";

        if (!mail($to, $subject, $message, $headers)) {
            error_log("Email failed to send to $to");
        }
    }

    header("Location: orders.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Detail - Tailor Stitch</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <h2>Staff Panel</h2>
        <a href="dashboard.php">
            <i class="ri-dashboard-line"></i>
            Dashboard
        </a>
        <a href="orders.php">
            <i class="ri-file-list-3-line"></i>
            View Orders
        </a>
        <a href="../logout.php">
            <i class="ri-logout-box-line"></i>
            Logout
        </a>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div class="flex items-center gap-2">
                <a href="orders.php" class="btn btn-secondary">
                    <i class="ri-arrow-left-line"></i>
                    Back to Orders
                </a>
                <h3>Order Details</h3>
            </div>
        </div>

        <div class="grid-container" style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
            <div class="dashboard-box">
                <div class="flex items-center justify-between mb-4">
                    <h3>Order Information</h3>
                    <span class="status-badge <?php echo strtolower($order["status"]); ?>">
                        <?php echo htmlspecialchars($order["status"]); ?>
                    </span>
                </div>

                <div class="grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                    <div class="info-group">
                        <label>Client Name</label>
                        <p><?php echo htmlspecialchars($order["client_name"]); ?></p>
                    </div>
                    <div class="info-group">
                        <label>Order Type</label>
                        <p><?php echo htmlspecialchars($order["cloth_type"]); ?></p>
                    </div>
                    <div class="info-group">
                        <label>Material</label>
                        <p><?php echo htmlspecialchars($order["cloth_material"]); ?></p>
                    </div>
                    <div class="info-group">
                        <label>Created Date</label>
                        <p><?php echo date('M d, Y', strtotime($order["created_at"])); ?></p>
                    </div>
                </div>
            </div>

            <div class="dashboard-box">
                <h3 class="mb-4">Actions</h3>
                <?php if ($order["status"] == "Pending" || $order["status"] == "Successful"): ?>
                    <button type="button" class="btn btn-success w-full mb-3" onclick="openEmailPreview()">
                        <i class="ri-check-line"></i>
                        <?php echo $order["status"] == "Pending" ? "Complete Order" : "Send Email"; ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-4">
            <div class="dashboard-box">
                <h3 class="mb-4">Measurements</h3>
                
                <?php if ($order["cloth_type"] == "Shirt" || $order["cloth_type"] == "Both"): ?>
                    <div class="measurement-section">
                        <h4 class="text-primary mb-2">Shirt Measurements</h4>
                        <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                            <div class="measurement-item">
                                <label>Neck</label>
                                <p><?php echo htmlspecialchars($measurements["neck"]); ?> inches</p>
                            </div>
                            <div class="measurement-item">
                                <label>Shoulder</label>
                                <p><?php echo htmlspecialchars($measurements["shoulder"]); ?> inches</p>
                            </div>
                            <div class="measurement-item">
                                <label>Chest</label>
                                <p><?php echo htmlspecialchars($measurements["chest"]); ?> inches</p>
                            </div>
                            <div class="measurement-item">
                                <label>Arm</label>
                                <p><?php echo htmlspecialchars($measurements["arm"]); ?> inches</p>
                            </div>
                            <div class="measurement-item">
                                <label>Wrist</label>
                                <p><?php echo htmlspecialchars($measurements["wrist"]); ?> inches</p>
                            </div>
                            <div class="measurement-item">
                                <label>Length</label>
                                <p><?php echo htmlspecialchars($measurements["length"]); ?> inches</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($order["cloth_type"] == "Pant" || $order["cloth_type"] == "Both"): ?>
                    <div class="measurement-section mt-4">
                        <h4 class="text-primary mb-2">Pant Measurements</h4>
                        <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                            <div class="measurement-item">
                                <label>Waist</label>
                                <p><?php echo htmlspecialchars($measurements["waist"]); ?> inches</p>
                            </div>
                            <div class="measurement-item">
                                <label>Hip</label>
                                <p><?php echo htmlspecialchars($measurements["hip"]); ?> inches</p>
                            </div>
                            <div class="measurement-item">
                                <label>Length</label>
                                <p><?php echo htmlspecialchars($measurements["pant_length"]); ?> inches</p>
                            </div>
                            <div class="measurement-item">
                                <label>Thighs</label>
                                <p><?php echo htmlspecialchars($measurements["thighs"]); ?> inches</p>
                            </div>
                            <div class="measurement-item">
                                <label>Calfs</label>
                                <p><?php echo htmlspecialchars($measurements["calfs"]); ?> inches</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Email Preview Modal -->
    <div id="emailPreviewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Email Preview</h3>
                <button onclick="closeEmailPreview()" class="close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <div class="email-preview">
                    <div class="email-field">
                        <label>From:</label>
                        <p>no-reply@tailorstitch.com</p>
                    </div>
                    <div class="email-field">
                        <label>To:</label>
                        <p><?php echo htmlspecialchars($order["client_email"]); ?></p>
                    </div>
                    <div class="email-field">
                        <label>Subject:</label>
                        <p>Your Order is Ready - Tailor Stitch</p>
                    </div>
                    <div class="email-field">
                        <label>Message:</label>
                        <div class="email-message">
                            <p>Dear <?php echo htmlspecialchars($order["client_name"]); ?>,</p>
                            <p>Your order is completed.</p>
                            <p>Details:</p>
                            <ul>
                                <li>Order Type: <?php echo htmlspecialchars($order["cloth_type"] ?? "N/A"); ?></li>
                                <li>Material: <?php echo htmlspecialchars($order["cloth_material"] ?? "N/A"); ?></li>
                            </ul>
                            <p>Thank you for choosing Tailor Stitch.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-actions">
                    <form method="POST" class="flex gap-3">
                        <input type="hidden" name="send_email" value="1">
                        <button type="submit" class="btn btn-success">
                            <i class="ri-mail-send-line"></i>
                            Send & Complete
                        </button>
                        <button type="submit" class="btn btn-secondary" onclick="this.form.send_email.value='0'">
                            Complete Without Email
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .info-group {
            margin-bottom: 1rem;
        }
        .info-group label {
            display: block;
            color: var(--gray-500);
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }
        .info-group p {
            color: var(--gray-900);
            font-weight: 500;
        }
        .measurement-item {
            background: var(--gray-50);
            padding: 1rem;
            border-radius: 0.5rem;
        }
        .measurement-item label {
            display: block;
            color: var(--gray-500);
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }
        .measurement-item p {
            color: var(--gray-900);
            font-weight: 500;
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .status-badge.new {
            background-color: #e0f2fe;
            color: #0369a1;
        }
        .status-badge.pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-badge.successful {
            background-color: #dcfce7;
            color: #166534;
        }
        .text-primary { color: var(--primary); }
        .w-full { width: 100%; }
        .mb-3 { margin-bottom: 0.75rem; }
        .flex { display: flex; }
        .gap-3 { gap: 0.75rem; }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        .modal-content {
            position: relative;
            background-color: #fff;
            margin: 2rem auto;
            padding: 0;
            border-radius: 0.5rem;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            animation: modalSlideIn 0.3s ease-out;
        }
        .modal-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-body {
            padding: 1.5rem;
        }
        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
        }
        .close-btn:hover {
            color: #111827;
        }
        .email-preview {
            background-color: #f9fafb;
            border-radius: 0.375rem;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }
        .email-field {
            margin-bottom: 1rem;
        }
        .email-field:last-child {
            margin-bottom: 0;
        }
        .email-field label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.25rem;
            display: block;
        }
        .email-field p {
            margin: 0;
            color: #4b5563;
        }
        .email-message {
            background-color: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            padding: 1rem;
        }
        .email-message p {
            margin: 0 0 0.75rem 0;
        }
        .email-message ul {
            margin: 0.5rem 0;
            padding-left: 1.5rem;
        }
        .email-message li {
            margin-bottom: 0.25rem;
        }
        .modal-actions {
            margin-top: 1.5rem;
            display: flex;
            justify-content: flex-end;
        }
        @keyframes modalSlideIn {
            from {
                transform: translateY(-10%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>

    <script>
        function openEmailPreview() {
            document.getElementById('emailPreviewModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeEmailPreview() {
            document.getElementById('emailPreviewModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            var modal = document.getElementById('emailPreviewModal');
            if (event.target == modal) {
                closeEmailPreview();
            }
        }
    </script>
</body>
</html>
