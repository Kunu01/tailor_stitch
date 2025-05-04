<?php
include "config/database.php";

// Determine which session to use based on role selection or type parameter
$login_type = $_POST['role'] ?? $_GET['type'] ?? '';
if ($login_type === 'admin') {
    include "admin_auth.php";
} elseif ($login_type === 'staff') {
    include "staff_auth.php";
} else {
    // Start a temporary session for the login page
    session_name('login_session');
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if already logged in
if ($login_type === 'admin' && isset($_SESSION["role"]) && $_SESSION["role"] === "admin") {
    header("Location: admin/dashboard.php");
    exit();
} elseif ($login_type === 'staff' && isset($_SESSION["role"]) && $_SESSION["role"] === "staff") {
    header("Location: staff/dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $role = $_POST["role"];

    if (empty($username) || empty($password) || empty($role)) {
        $error = "All fields are required";
    } else {
        if ($role === "admin") {
            // Admin login
            $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                // Temporary: Direct password comparison since passwords are not hashed
                if ($password === $admin["password"]) {
                    session_name('admin_session');
                    session_start();
                    set_admin_session($admin["id"], $admin["name"]);
                    header("Location: admin/dashboard.php");
                    exit();
                } else {
                    $error = "Invalid password";
                }
            } else {
                $error = "Admin not found";
            }
        } else {
            // Staff login
            $stmt = $conn->prepare("SELECT * FROM staff WHERE staff_id = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $staff = $result->fetch_assoc();
                if (password_verify($password, $staff["password"])) {
                    session_name('staff_session');
                    session_start();
                    set_staff_session($staff["id"], $staff["name"]);
                    header("Location: staff/dashboard.php");
                    exit();
                } else {
                    $error = "Invalid password";
                }
            } else {
                $error = "Staff ID not found";
            }
        }
    }
}

// Get messages from logout or timeout
$logged_out = isset($_GET['logged_out']) ? true : false;
$timeout = isset($_GET['timeout']) ? true : false;
$from = isset($_GET['from']) ? $_GET['from'] : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Tailor Stitch</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>Tailor Stitch</h1>
                <p>Sign in to your account</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($logged_out): ?>
                <div class="alert alert-success">
                    <i class="ri-checkbox-circle-line"></i>
                    You have been successfully logged out
                </div>
            <?php endif; ?>

            <?php if ($timeout): ?>
                <div class="alert alert-warning">
                    <i class="ri-time-line"></i>
                    Your session has expired. Please login again
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="role">Login As</label>
                    <div class="role-selector">
                        <label class="role-option">
                            <input type="radio" name="role" value="admin" 
                                   <?php echo ($from === 'admin') ? 'checked' : ''; ?>>
                            <span class="role-box">
                                <i class="ri-admin-line"></i>
                                Admin
                            </span>
                        </label>
                        <label class="role-option">
                            <input type="radio" name="role" value="staff" 
                                   <?php echo ($from === 'staff') ? 'checked' : ''; ?>>
                            <span class="role-box">
                                <i class="ri-user-line"></i>
                                Staff
                            </span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="username">Username / Staff ID</label>
                    <div class="input-group">
                        <i class="ri-user-line"></i>
                        <input type="text" name="username" id="username" placeholder="Enter your username or staff ID">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <i class="ri-lock-line"></i>
                        <input type="password" name="password" id="password" placeholder="Enter your password">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-full">
                    <i class="ri-login-box-line"></i>
                    Sign In
                </button>
            </form>
        </div>
    </div>

    <style>
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            padding: 1rem;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
        }
        .login-box {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h1 {
            color: var(--primary);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .login-header p {
            color: var(--gray-600);
        }
        .role-selector {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .role-option {
            cursor: pointer;
        }
        .role-option input {
            display: none;
        }
        .role-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        .role-box i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--gray-500);
        }
        .role-option input:checked + .role-box {
            border-color: var(--primary);
            background: var(--primary-50);
        }
        .role-option input:checked + .role-box i {
            color: var(--primary);
        }
        .input-group {
            position: relative;
        }
        .input-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
        }
        .input-group input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid var(--gray-200);
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        .input-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
        }
        .alert-success {
            background: #dcfce7;
            color: #166534;
        }
        .alert-warning {
            background: #fef3c7;
            color: #92400e;
        }
        .w-full {
            width: 100%;
        }
    </style>

    <script>
        // Auto-focus username field on load
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });

        // Update placeholder based on selected role
        document.querySelectorAll('input[name="role"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                const usernamePlaceholder = this.value === 'admin' 
                    ? 'Enter your username' 
                    : 'Enter your staff ID';
                document.getElementById('username').placeholder = usernamePlaceholder;
            });
        });
    </script>
</body>
</html>
