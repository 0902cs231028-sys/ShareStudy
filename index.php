<?php
session_start();

// 1. Auto-Redirect: If they are already logged in, skip this page!
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to ShareStudy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify_content: center;
            font-family: 'Poppins', sans-serif;
            color: white;
            overflow: hidden;
            text-align: center;
        }

        .glass-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 4rem 2rem;
            max-width: 800px;
            width: 90%;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            animation: floatUp 1s ease-out;
        }

        h1 {
            font-weight: 700;
            font-size: 3.5rem;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        p.lead {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2.5rem;
        }

        .btn-hero {
            padding: 12px 40px;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            margin: 10px;
            text-decoration: none;
        }

        .btn-primary-glass {
            background: rgba(255, 255, 255, 0.9);
            color: #764ba2;
        }
        .btn-primary-glass:hover {
            background: #fff;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255,255,255,0.4);
        }

        .btn-outline-glass {
            border: 2px solid rgba(255, 255, 255, 0.8);
            color: white;
        }
        .btn-outline-glass:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateY(-3px);
        }

        .features {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 3rem;
            flex-wrap: wrap;
        }
        .feature-item {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.8);
        }
        .feature-item i {
            display: block;
            font-size: 2rem;
            margin-bottom: 10px;
            color: #fff;
        }

        @keyframes floatUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <div class="glass-container">
        <h1><i class="fas fa-graduation-cap"></i> ShareStudy</h1>
        <p class="lead">The Ultimate Student Resource Hub</p>
        
        <div>
            <a href="login.php" class="btn-hero btn-primary-glass">Login</a>
            <a href="register.php" class="btn-hero btn-outline-glass">Join Now</a>
        </div>

        <div class="features">
            <div class="feature-item">
                <i class="fas fa-file-pdf"></i>
                File Sharing
            </div>
            <div class="feature-item">
                <i class="fas fa-comments"></i>
                Global Chat
            </div>
            <div class="feature-item">
                <i class="fas fa-shield-alt"></i>
                Secure & Fast
            </div>
        </div>
    </div>

</body>
</html>
