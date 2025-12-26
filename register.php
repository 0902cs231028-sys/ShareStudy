<?php
// 1. Start Session & Connect
session_start();
require_once 'includes/db_connect.php';

$message = "";
$msg_type = ""; // 'success' or 'danger'

// 2. Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Basic Validation
    if(empty($username) || empty($email) || empty($password)){
        $message = "All fields are required.";
        $msg_type = "danger";
    } else {
        try {
            // 3. Check if User Already Exists
            // We use a prepared statement to be secure
            $checkSql = "SELECT id FROM users WHERE username = :u OR email = :e";
            $stmt = $conn->prepare($checkSql);
            $stmt->execute(['u' => $username, 'e' => $email]);
            
            if($stmt->rowCount() > 0){
                $message = "Username or Email already taken.";
                $msg_type = "danger";
            } else {
                // 4. Create New User
                // Hashing password is MANDATORY for security
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // By default, new users are 'student' and 'light' theme
                $insertSql = "INSERT INTO users (username, email, password, role, theme) 
                              VALUES (:u, :e, :p, 'student', 'light')";
                
                $stmt = $conn->prepare($insertSql);
                
                if($stmt->execute(['u' => $username, 'e' => $email, 'p' => $hashed_password])){
                    $message = "Account created successfully! <a href='login.php' class='text-white fw-bold'>Login Here</a>";
                    $msg_type = "success";
                } else {
                    $message = "Something went wrong. Please try again.";
                    $msg_type = "danger";
                }
            }
        } catch(PDOException $e) {
            // 5. The "Bypass" - Catch errors so site doesn't crash
            // We log the error internally, but show user a generic message
            // error_log($e->getMessage()); // Optional: Log to server file
            $message = "System Error: Could not register. (Database Error)"; 
            $msg_type = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | ShareStudy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        /* Consistent Glass Theme */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify_content: center;
            font-family: 'Poppins', sans-serif;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            padding: 2rem;
            color: white;
            max-width: 450px;
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
        }
        .links a:hover {
            color: white;
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="glass-card">
        <h2 class="text-center mb-4">Join ShareStudy</h2>
        
        <?php if(!empty($message)): ?>
            <div class="alert alert-<?php echo $msg_type; ?> text-center py-2" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="mb-3">
                <input type="text" name="username" class="form-control" placeholder="Choose Username" required>
            </div>
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Your Email" required>
            </div>
            <div class="mb-4">
                <input type="password" name="password" class="form-control" placeholder="Create Password" required>
            </div>
            <button type="submit" class="btn btn-custom">Sign Up</button>
        </form>

        <div class="links text-center mt-3">
            <p class="mb-0">Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>

</body>
</html>
