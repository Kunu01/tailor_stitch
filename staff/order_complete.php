<?php
include "../config/database.php";
include "../staff_auth.php";
include __DIR__ . '/../config/email.php'; // Modified path resolution

$session = check_staff_auth();
$staff_id = $session["user_id"];
$order_id = $_GET["order_id"] ?? null;

if (!$order_id) {
    header("Location: orders.php");
    exit();
}

// Check if this staff member has accepted this order
$order = $conn->query("SELECT * FROM orders WHERE id = $order_id AND staff_id = $staff_id AND status = 'Pending'")->fetch_assoc();
if (!$order) {
    $_SESSION['error_message'] = "Invalid order or you don't have permission to complete this order.";
    header("Location: orders.php");
    exit();
}

$client = $conn->query("SELECT * FROM clients WHERE id = {$order['client_id']}")->fetch_assoc();

if (isset($_GET['complete'])) {
    // Update order status
    $stmt = $conn->prepare("UPDATE orders SET status = 'Successful', updated_at = NOW() WHERE id = ? AND staff_id = ? AND status = 'Pending'");
    $stmt->bind_param("ii", $order_id, $staff_id);
    $success = $stmt->execute();
    $stmt->close();

    if ($success) {
        // Send email notification
        $email_content = format_order_email($client["name"], [
            'cloth_type' => $order["cloth_type"],
            'cloth_material' => $order["cloth_material"]
        ]);
        
        $result = send_email(
            $client["email"],
            "Your Order is Ready - Tailor Stitch",
            $email_content
        );

        if ($result['success']) {
            $_SESSION['success_message'] = "Order marked as complete. Email notification sent to " . $client['name'];
        } else {
            $_SESSION['error_message'] = "Order marked as complete but failed to send email notification.";
        }
    } else {
        $_SESSION['error_message'] = "Failed to complete order.";
    }
    
    header("Location: orders.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Complete Order - Tailor Stitch</title>
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
            Orders
        </a>
        <a href="../logout.php?type=staff">
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
                <h3>Complete Order</h3>
            </div>
        </div>

        <div class="dashboard-box">
            <div class="flex items-center justify-between mb-4">
                <h3>Order #<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></h3>
                <span class="status-badge pending">
                    <?php echo htmlspecialchars($order["status"]); ?>
                </span>
            </div>

            <div class="client-info mb-4">
                <h4 class="text-gray-600 mb-2">Client Information</h4>
                <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="info-item">
                        <label>Name</label>
                        <p><?php echo htmlspecialchars($client["name"]); ?></p>
                    </div>
                    <div class="info-item">
                        <label>Email</label>
                        <p><?php echo htmlspecialchars($client["email"]); ?></p>
                    </div>
                    <div class="info-item">
                        <label>Phone</label>
                        <p><?php echo htmlspecialchars($client["phone"]); ?></p>
                    </div>
                </div>
            </div>

            <div class="order-info mb-4">
                <h4 class="text-gray-600 mb-2">Order Details</h4>
                <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="info-item">
                        <label>Order Type</label>
                        <p><?php echo htmlspecialchars($order["cloth_type"]); ?></p>
                    </div>
                    <div class="info-item">
                        <label>Material</label>
                        <p><?php echo htmlspecialchars($order["cloth_material"]); ?></p>
                    </div>
                    <div class="info-item">
                        <label>Date Created</label>
                        <p><?php echo date('M d, Y', strtotime($order["created_at"])); ?></p>
                    </div>
                </div>
            </div>

            <div class="completion-options mt-4">
                <h4 class="text-gray-600 mb-2">Complete Order</h4>
                <div class="flex gap-2">
                    <button onclick="showEmailPreview()" class="btn btn-primary">
                        <i class="ri-mail-send-line"></i>
                        Complete Order
                    </button>
                </div>
            </div>

            <!-- Email Preview Modal -->
            <div id="emailPreviewModal" class="modal hidden">
                <div class="modal-content">
                    <h3>Email Preview</h3>
                    <div class="email-preview">
                        <div class="email-field">
                            <label>From:</label>
                            <p>no-reply@tailorstitch.com</p>
                        </div>
                        <div class="email-field">
                            <label>To:</label>
                            <p><?php echo htmlspecialchars($client["email"]); ?></p>
                        </div>
                        <div class="email-field">
                            <label>Subject:</label>
                            <p>Your Order is Ready - Tailor Stitch</p>
                        </div>
                        <div class="email-field">
                            <label>Message:</label>
                            <pre class="email-message"><?php 
                                echo htmlspecialchars(format_order_email($client["name"], [
                                    'cloth_type' => $order["cloth_type"],
                                    'cloth_material' => $order["cloth_material"]
                                ]));
                            ?></pre>
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button onclick="completeOrder('send_email')" class="btn btn-primary">
                            <i class="ri-mail-send-line"></i>
                            Complete & Send Email
                        </button>
                        <button onclick="completeOrder('no_email')" class="btn btn-secondary">
                            <i class="ri-check-line"></i>
                            Complete Without Email
                        </button>
                        <button onclick="closeEmailPreview()" class="btn btn-tertiary">
                            <i class="ri-close-line"></i>
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .info-item {
            background: var(--gray-50);
            padding: 1rem;
            border-radius: 0.5rem;
        }
        .info-item label {
            display: block;
            color: var(--gray-500);
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }
        .info-item p {
            color: var(--gray-900);
            font-weight: 500;
            margin: 0;
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
        .completion-options {
            background: var(--gray-50);
            padding: 1.5rem;
            border-radius: 0.5rem;
        }
        .text-gray-600 { color: var(--gray-600); }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mt-4 { margin-top: 1rem; }

        /* Modal Styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .modal.hidden {
            display: none;
        }
        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }
        .email-preview {
            margin: 1.5rem 0;
        }
        .email-field {
            margin-bottom: 1rem;
        }
        .email-field label {
            font-weight: 500;
            color: var(--gray-600);
            display: inline-block;
            width: 80px;
        }
        .email-field p {
            display: inline;
            margin-left: 0.5rem;
        }
        .email-message {
            background: var(--gray-50);
            padding: 1rem;
            border-radius: 0.5rem;
            white-space: pre-wrap;
            font-family: inherit;
            margin: 0.5rem 0 0 80px;
        }
        .modal-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1.5rem;
            justify-content: flex-end;
        }
        .btn-tertiary {
            background: var(--gray-200);
            color: var(--gray-700);
        }
        .btn-tertiary:hover {
            background: var(--gray-300);
        }
    </style>

    <script>
        function showEmailPreview() {
            document.getElementById('emailPreviewModal').classList.remove('hidden');
        }

        function closeEmailPreview() {
            document.getElementById('emailPreviewModal').classList.add('hidden');
        }

        function completeOrder(type) {
            const orderId = <?php echo $order_id; ?>;
            window.location.href = `?order_id=${orderId}&complete=${type}`;
        }
    </script>
</body>
</html>
