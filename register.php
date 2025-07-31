<?php
require_once "includes/db.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $role = $_POST["role"];
    $account_no = rand(10000000, 99999999);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, account_no, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $password, $account_no, $role);

    if ($stmt->execute()) {
        $success = true;
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank App - Register</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f8ff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        
        .window {
            width: 350px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 2px solid #9ec1d4;
            position: relative;
        }
        
        .title-bar {
            background-color: #9ec1d4;
            color: white;
            padding: 8px 12px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: move;
        }
        
        .window-controls {
            display: flex;
            gap: 8px;
        }
        
        .control-btn {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            cursor: pointer;
        }
        
        .close { background-color: #ff5f56; }
        .minimize { background-color: #ffbd2e; }
        .maximize { background-color: #27c93f; }
        
        .window-content {
            padding: 20px;
        }
        
        h2 {
            color: #9ec1d4;
            margin-top: 0;
            text-align: center;
        }
        
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        input, select {
            padding: 10px;
            border: 1px solid #9ec1d4;
            border-radius: 5px;
            font-size: 14px;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #7a9bb1;
        }
        
        button {
            background-color: #9ec1d4;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #7a9bb1;
        }
        
        .login-link {
            text-align: center;
            margin-top: 15px;
            color: #666;
        }
        
        .login-link a {
            color: #9ec1d4;
            text-decoration: none;
            font-weight: bold;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .error {
            color: #ff5f56;
            text-align: center;
            margin-bottom: 10px;
        }
        
        /* Success Popup Styles */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }
        
        .popup-overlay.active {
            opacity: 1;
            pointer-events: all;
        }
        
        .popup-window {
            background-color: white;
            border-radius: 10px;
            width: 300px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            border: 2px solid #d4afb9;
            overflow: hidden;
        }
        
        .popup-title-bar {
            background-color: #d4afb9;
            color: white;
            padding: 8px 12px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: move;
        }
        
        .popup-content {
            padding: 20px;
            text-align: center;
        }
        
        .popup-title {
            color: #d4afb9;
            font-weight: bold;
            margin-top: 0;
        }
        
        .popup-btn {
            background-color: #d4afb9;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 15px;
            transition: background-color 0.2s;
        }
        
        .popup-btn:hover {
            background-color: #a78ba2;
        }
    </style>
</head>
<body>
    <div class="window">
        <div class="title-bar">
            <span>Bank App</span>
            <div class="window-controls">
                <div class="control-btn minimize" onclick="minimizeWindow()"></div>
                <div class="control-btn maximize" onclick="maximizeWindow()"></div>
                <div class="control-btn close" onclick="window.location.href='login.php'"></div>
            </div>
        </div>
        <div class="window-content">
            <h2>Register</h2>
            
            <?php if (isset($error)): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" id="registerForm">
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <select name="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="customer">Customer</option>
                    <option value="teller">Teller</option>
                </select>
                <button type="submit">Register</button>
            </form>
            
            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>

    <div class="popup-overlay" id="successPopup">
        <div class="popup-window" id="popupWindow">
            <div class="popup-title-bar">
                <div class="window-controls">
                    <div class="control-btn minimize" onclick="minimizePopup()"></div>
                    <div class="control-btn maximize" onclick="maximizePopup()"></div>
                    <div class="control-btn close" onclick="closePopup()"></div>
                </div>
            </div>
            <div class="popup-content">
                <h3 class="popup-title">Success!</h3>
                <p>Your account has been created successfully.</p>
                <button class="popup-btn" onclick="window.location.href='login.php'">Go to Login</button>
            </div>
        </div>
    </div>

    <script>
        <?php if (isset($success) && $success): ?>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('successPopup').classList.add('active');
                document.getElementById('registerForm').reset();
            });
        <?php endif; ?>
        
    </script>
</body>
</html>