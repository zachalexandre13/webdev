<?php
require_once "includes/db.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["role"] = $user["role"];
        header("Location: dashboard.php");
    } else {
        $error = "Invalid login credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank App - Login</title>
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
            border: 2px solid #d4afb9;
        }
        
        .title-bar {
            background-color: #d4afb9;
            color: white;
            padding: 8px 12px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .window-controls {
            display: flex;
            gap: 8px;
        }
        
        .control-btn {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }
        
        .close { background-color: #ff5f56; }
        .minimize { background-color: #ffbd2e; }
        .maximize { background-color: #27c93f; }
        
        .window-content {
            padding: 20px;
        }
        
        h2 {
            color: #d4afb9;
            margin-top: 0;
            text-align: center;
        }
        
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        input {
            padding: 10px;
            border: 1px solid #d4afb9;
            border-radius: 5px;
            font-size: 14px;
        }
        
        input:focus {
            outline: none;
            border-color: #a78ba2;
        }
        
        button {
            background-color: #d4afb9;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #a78ba2;
        }
        
        .register-link {
            text-align: center;
            margin-top: 15px;
            color: #666;
        }
        
        .register-link a {
            color: #d4afb9;
            text-decoration: none;
            font-weight: bold;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .error {
            color: #ff5f56;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="window">
        <div class="title-bar">
            <span>Bank App</span>
            <div class="window-controls">
                <div class="control-btn minimize"></div>
                <div class="control-btn maximize"></div>
                <div class="control-btn close"></div>
            </div>
        </div>
        <div class="window-content">
            <h2>Login</h2>
            
            <?php if (isset($error)): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            
            <div class="register-link">
                No account? <a href="register.php">Register here</a>
            </div>
        </div>
    </div>
</body>
</html>