<?php
require_once "includes/db.php";
require_once "includes/auth.php";

if ($_SESSION["role"] !== "teller") {
    echo "Access denied.";
    exit();
}

$res = $conn->query("SELECT u.name, p.customers_served, p.total_wait_time
                    FROM performance p JOIN users u ON u.id = p.teller_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank App - Performance</title>
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
            width: 600px;
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

        .performance-item {
            background-color: #f8f4f7;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .performance-item h3 {
            color: #a78ba2;
            margin-top: 0;
            border-bottom: 1px solid #e0d0dd;
            padding-bottom: 8px;
        }

        .performance-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .stat {
            background-color: white;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .stat-label {
            font-weight: bold;
            color: #a78ba2;
            font-size: 14px;
        }

        .stat-value {
            font-size: 18px;
            color: #333;
        }

        .back-btn {
            background-color: #a78ba2;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 20px;
            display: inline-block;
            transition: background-color 0.2s;
            text-decoration: none;
        }

        .back-btn:hover {
            background-color: #d4afb9;
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

            .performance-stats {
                grid-template-columns: 1fr;
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

                <div class="icon" onclick="window.location.href='teller.php'">
                    <img src="https://cdn-icons-png.flaticon.com/512/2945/2945506.png" class="icon-img">
                    <div class="icon-label">Teller Panel</div>
                </div>

                <div class="icon" onclick="window.location.href='performance.php'">
                    <img src="https://cdn-icons-png.freepik.com/256/12454/12454072.png" class="icon-img">
                    <div class="icon-label">Performance</div>
                </div>
            </div>

            <div class="window">
                <div class="window-title-bar">
                    <span>Teller Performance</span>
                    <div class="window-controls">
                        <div class="control-btn minimize"></div>
                        <div class="control-btn maximize"></div>
                        <div class="control-btn close" onclick="window.location.href='dashboard.php'"></div>
                    </div>
                </div>
                <div class="window-content">
                    <h2>Teller Performance Metrics</h2>
                    
                    <?php while ($row = $res->fetch_assoc()): ?>
                        <div class="performance-item">
                            <h3><?= htmlspecialchars($row['name']) ?></h3>
                            <div class="performance-stats">
                                <div class="stat">
                                    <div class="stat-label">Customers Served</div>
                                    <div class="stat-value"><?= $row['customers_served'] ?></div>
                                </div>
                                <div class="stat">
                                    <div class="stat-label">Average Wait Time</div>
                                    <div class="stat-value">
                                        <?php if ($row['customers_served'] > 0): ?>
                                            <?= number_format($row['total_wait_time'] / $row['customers_served'], 2) ?> sec
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
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