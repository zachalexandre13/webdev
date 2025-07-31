<?php
require_once "includes/db.php";
require_once "includes/auth.php";

if ($_SESSION["role"] !== "customer") {
    echo "Access denied.";
    exit();
}

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = floatval($_POST["amount"]);

    $conn->query("INSERT INTO transactions (user_id, type, amount) 
                  VALUES ($user_id, 'deposit', $amount)");
    $transaction_id = $conn->insert_id;

    $check = $conn->query("SELECT * FROM queue WHERE user_id = $user_id AND status = 'waiting'");
    if ($check->num_rows == 0) {
        $estimated_time = rand(3, 10);
        $conn->query("INSERT INTO queue (user_id, transaction_id, estimated_time) 
                      VALUES ($user_id, $transaction_id, $estimated_time)");
    }

    $success = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank App - Deposit</title>
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #d4e4f7;
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
            color: #333;
            display: flex;
            flex-direction: column;
        }

        .desktop {
            flex: 1;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .desktop-area {
            flex: 1;
            position: relative;
            overflow: auto;
            padding: 20px;
        }

        .taskbar {
            background-color: #a78ba2;
            padding: 8px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 -2px 5px rgba(0,0,0,0.2);
            z-index: 100;
            flex-shrink: 0;
        }

        .start-button {
            background-color: #d4afb9;
            color: white;
            border: none;
            padding: 5px 12px;
            border-radius: 3px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .time {
            color: white;
            font-size: 14px;
        }

        .desktop-icons {
            display: grid;
            grid-template-columns: repeat(auto-fill, 80px);
            grid-auto-rows: 100px;
            gap: 20px;
            align-content: start;
            height: 100%;
        }

        .icon {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s;
            width: 80px;
            user-select: none;
        }

        .icon:hover {
            transform: scale(1.05);
        }

        .icon-img {
            width: 50px;
            height: 50px;
            background-color: #9ec1d4;
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 5px;
            color: white;
            font-size: 24px;
        }

        .icon-label {
            font-size: 12px;
            color: #333;
            word-break: break-word;
            line-height: 1.2;
        }

        .window {
            position: absolute;
            width: 400px;
            min-width: 300px;
            max-width: 90%;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 2px solid #a78ba2;
            top: 100px;
            left: 100px;
            z-index: 10;
        }

        .window-title-bar {
            background-color: #a78ba2;
            color: white;
            padding: 8px 12px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: move;
            user-select: none;
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
            overflow: auto;
            max-height: 400px;
        }
        
        h2 {
            color: #a78ba2;
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
            border: 1px solid #a78ba2;
            border-radius: 5px;
            font-size: 16px;
        }
        
        input:focus {
            outline: none;
            border-color: #d4afb9;
        }
        
        button {
            background-color: #a78ba2;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: background-color 0.2s;
        }
        
        button:hover {
            background-color: #d4afb9;
        }

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
            border-radius: 8px;
            width: 300px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            border: 2px solid #27c93f;
            overflow: hidden;
        }
        
        .popup-title-bar {
            background-color: #27c93f;
            color: white;
            padding: 8px 12px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .popup-content {
            padding: 20px;
            text-align: center;
        }
        
        .popup-btn {
            background-color: #27c93f;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 15px;
        }

        @media (max-width: 600px) {
            .desktop-icons {
                grid-template-columns: repeat(auto-fill, 70px);
                gap: 15px;
            }
            
            .icon {
                width: 70px;
            }
            
            .window {
                width: 90%;
                left: 5%;
                top: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="desktop">
        <div class="desktop-area">
            <div class="desktop-icons">
                <div class="icon" onclick="window.location.href='dashboard.php'">
                    <img src="https://cdn-icons-png.flaticon.com/512/2620/2620936.png" class="icon-img">
                    <div class="icon-label">Dashboard</div>
                </div>

                <div class="icon" onclick="window.location.href='deposit.php'">
                    <img src="https://png.pngtree.com/png-vector/20220611/ourmid/pngtree-wallet-deposit-icon-outline-vector-png-image_4983454.png" class="icon-img">
                    <div class="icon-label">Deposit</div>
                </div>

                <div class="icon" onclick="window.location.href='withdraw.php'">
                    <img src="https://images.vexels.com/media/users/3/145760/isolated/preview/ae110700c75dee2112cb276b150d79e2-money-withdraw.png?w=360" class="icon-img">
                    <div class="icon-label">Withdraw</div>
                </div>

                <div class="icon" onclick="window.location.href='transactions.php'">
                    <img src="https://png.pngtree.com/png-vector/20220517/ourmid/pngtree-mobile-transfer-icon-color-flat-png-image_4665878.png" class="icon-img">
                    <div class="icon-label">Transactions</div>
                </div>

                <?php if ($_SESSION["role"] === "teller"): ?>
                    <div class="icon" onclick="window.location.href='teller.php'">
                        <img src="https://cdn-icons-png.flaticon.com/512/2945/2945506.png" class="icon-img">
                        <div class="icon-label">Teller Panel</div>
                    </div>

                    <div class="icon" onclick="window.location.href='performance.php'">
                        <img src="https://cdn-icons-png.freepik.com/256/12454/12454072.png" class="icon-img">
                        <div class="icon-label">Performance</div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="window">
                <div class="window-title-bar">
                    <span>Deposit</span>
                    <div class="window-controls">
                        <div class="control-btn minimize"></div>
                        <div class="control-btn maximize"></div>
                        <div class="control-btn close" onclick="window.location.href='dashboard.php'"></div>
                    </div>
                </div>
                <div class="window-content">
                    <h2>Deposit Funds</h2>
                    <form method="POST">
                        <input type="number" name="amount" placeholder="Enter amount" required step="0.01" min="0.01">
                        <button type="submit">Deposit</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="taskbar">
            <button class="start-button" onclick="window.location.href='logout.php'">
                <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Windows_icon_logo.png" alt="Start" width="16" height="16">
            </button>
            <div class="time" id="time"></div>
        </div>
    </div>

    <?php if (isset($success) && $success): ?>
    <div class="popup-overlay active" id="successPopup">
        <div class="popup-window">
            <div class="popup-title-bar">
                <span>Success</span>
                <div class="window-controls">
                    <div class="control-btn close" onclick="document.getElementById('successPopup').classList.remove('active')"></div>
                </div>
            </div>
            <div class="popup-content">
                <p>Your deposit request has been submitted!</p>
                <p>Estimated processing time: <?= $estimated_time ?? 5 ?> minutes</p>
                <button class="popup-btn" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            document.getElementById('time').textContent = timeString;
        }
        
        setInterval(updateTime, 1000);
        updateTime();

        const windowElement = document.querySelector('.window');
        let isDragging = false;
        let offsetX, offsetY;

        windowElement.querySelector('.window-title-bar').addEventListener('mousedown', (e) => {
            isDragging = true;
            offsetX = e.clientX - windowElement.getBoundingClientRect().left;
            offsetY = e.clientY - windowElement.getBoundingClientRect().top;
            windowElement.style.cursor = 'grabbing';
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            
            const desktopRect = document.querySelector('.desktop-area').getBoundingClientRect();
            const windowRect = windowElement.getBoundingClientRect();
            
            let newLeft = e.clientX - offsetX - desktopRect.left;
            let newTop = e.clientY - offsetY - desktopRect.top;
            
            newLeft = Math.max(0, Math.min(newLeft, desktopRect.width - windowRect.width));
            newTop = Math.max(0, Math.min(newTop, desktopRect.height - windowRect.height));
            
            windowElement.style.left = `${newLeft}px`;
            windowElement.style.top = `${newTop}px`;
        });

        document.addEventListener('mouseup', () => {
            isDragging = false;
            windowElement.style.cursor = 'default';
        });
    </script>
</body>
</html>