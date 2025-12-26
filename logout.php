<?php
session_start();
// Destroy all session data
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out | ShareStudy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify_content: center;
            font-family: 'Poppins', sans-serif;
            overflow: hidden;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            padding: 3rem;
            text-align: center;
            color: white;
            animation: fadeIn 0.8s ease-out;
            max-width: 400px;
            width: 90%;
        }
        .check-icon {
            font-size: 4rem;
            color: #fff;
            margin-bottom: 1rem;
            display: inline-block;
            animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.3s backwards;
        }
        h2 { font-weight: 600; margin-bottom: 0.5rem; }
        p { color: rgba(255, 255, 255, 0.8); }
        
        .loader {
            height: 4px;
            width: 100%;
            background: rgba(255,255,255,0.2);
            margin-top: 20px;
            border-radius: 4px;
            overflow: hidden;
        }
        .loader-bar {
            height: 100%;
            background: #fff;
            width: 0%;
            animation: load 3s linear forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes popIn {
            from { opacity: 0; transform: scale(0.5); }
            to { opacity: 1; transform: scale(1); }
        }
        @keyframes load {
            from { width: 100%; }
            to { width: 0%; }
        }
    </style>
</head>
<body>

    <div class="glass-card">
        <div class="check-icon">👋</div>
        <h2>See You Soon!</h2>
        <p>You have been safely logged out.</p>
        
        <div class="loader">
            <div class="loader-bar"></div>
        </div>
        
        <p class="mt-3" style="font-size: 0.8rem;">Redirecting to login...</p>
    </div>

    <script>
        // Redirect after 3 seconds
        setTimeout(function(){
            window.location.href = 'login.php';
        }, 3000);
    </script>
</body>
</html>
