<?php
// 1. Start Session & Connect Database
session_start();
require_once 'includes/db_connect.php';

$error_msg = "";

// 2. Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_user = trim($_POST['username']);
    $input_pass = trim($_POST['password']);

    if(empty($input_user) || empty($input_pass)){
        $error_msg = "Please enter both username and password.";
    } else {
        // Prepare statement to prevent SQL Injection
        // We allow login via Username OR Email
        $sql = "SELECT id, username, password, role, is_blocked FROM users WHERE username = :u OR email = :u";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['u' => $input_user]);
        $user = $stmt->fetch();

        if ($user) {
            // 3. Verify Password & Check Status
            if (password_verify($input_pass, $user['password'])) {
                if ($user['is_blocked'] == 1) {
                    $error_msg = "Your account has been blocked by the Admin.";
                } else {
                    // Login Success! Set Session Variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    // Redirect based on Role
                    if ($user['role'] === 'admin') {
                        header("Location: admin/dashboard.php");
                    } else {
                        header("Location: dashboard.php");
                    }
                    exit;
                }
            } else {
                $error_msg = "Invalid password.";
            }
        } else {
            $error_msg = "User not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | ShareStudy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        /* Custom Stylish CSS */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align_items: center;
            justify_content: center;
            font-family: 'Poppins', sans-serif;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px); /* The Glass Effect */
            -webkit-backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            padding: 2rem;
            color: white;
            max-width: 400px;
            width: 100%;
        }
        .form-control {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 12px;
        }
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.3);
            box-shadow: none;
            color: white;
        }
        .btn-custom {
            background-color: #ffffff;
            color: #667eea;
            font-weight: bold;
            border-radius: 50px;
            padding: 10px 20px;
            width: 100%;
            transition: 0.3s;
        }
        .btn-custom:hover {
            background-color: #f0f0f0;
            transform: translateY(-2px);
        }
        .links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 0.9rem;
        }
        .links a:hover {
            color: white;
            text-decoration: underline;
        }
        .alert-custom {
            background: rgba(255, 0, 0, 0.2);
            border: 1px solid rgba(255, 0, 0, 0.3);
            color: #ffcccc;
        }
    </style>
</head>
<body>

    <div class="glass-card">
        <h2 class="text-center mb-4">Welcome Back</h2>
        
        <?php if(!empty($error_msg)): ?>
            <div class="alert alert-custom text-center py-2"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <input type="text" name="username" class="form-control" placeholder="Username or Email" required>
            </div>
            <div class="mb-4">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-custom">Login</button>
        </form>

        <div class="links text-center mt-3">
            <p class="mb-0">Don't have an account? <a href="register.php">Sign Up</a></p>
        </div>
    </div>

</body>
</html>
