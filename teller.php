<?php
require_once "includes/db.php";
require_once "includes/auth.php";

if ($_SESSION["role"] !== "teller") {
    echo "Access denied.";
    exit();
}

$teller_id = $_SESSION["user_id"];
$step = $_POST["step"] ?? "choose";
$algorithm = $_POST["algorithm"] ?? null;

if (!isset($_SESSION['rr_index'])) {
    $_SESSION['rr_index'] = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank App - Teller Panel</title>
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
            width: 700px;
            min-width: 300px;
            max-width: 90%;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 2px solid #d4afb9;
            top: 100px;
            left: 100px;
            z-index: 10;
        }

        .window-title-bar {
            background-color: #d4afb9;
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
            max-height: 500px;
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
            margin: 20px 0;
        }

        select, button {
            padding: 10px;
            border-radius: 5px;
            font-size: 16px;
        }

        select {
            border: 1px solid #d4afb9;
            background-color: white;
        }

        select:focus {
            outline: none;
            border-color: #a78ba2;
        }

        button {
            background-color: #d4afb9;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        button:hover {
            background-color: #a78ba2;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 14px;
        }

        th {
            background-color: #d4afb9;
            color: white;
            padding: 10px;
            text-align: left;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #f0e6ed;
        }

        tr:nth-child(even) {
            background-color: #faf5f8;
        }

        tr:hover {
            background-color: #f0e6ed;
        }

        .no-customers {
            text-align: center;
            color: #666;
            padding: 20px;
        }

        .success-message {
            color: #2e7d32;
            background-color: #e8f5e9;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }

        .error-message {
            color: #c62828;
            background-color: #ffebee;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }

        .processing-message {
            color: #1565c0;
            background-color: #e3f2fd;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }

        .algorithm-display {
            font-weight: bold;
            color: #a78ba2;
            margin: 10px 0;
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

            table {
                font-size: 12px;
            }

            th, td {
                padding: 8px 5px;
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

                <?php if ($_SESSION["role"] !== "teller"): ?>
                    <div class="icon" onclick="window.location.href='deposit.php'">
                        <img src="https://png.pngtree.com/png-vector/20220611/ourmid/pngtree-wallet-deposit-icon-outline-vector-png-image_4983454.png" class="icon-img">
                        <div class="icon-label">Deposit</div>
                    </div>

                    <div class="icon" onclick="window.location.href='withdraw.php'">
                        <img src="https://images.vexels.com/media/users/3/145760/isolated/preview/ae110700c75dee2112cb276b150d79e2-money-withdraw.png?w=360" class="icon-img">
                        <div class="icon-label">Withdraw</div>
                    </div>
                <?php endif; ?>

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
                    <span>Teller Panel</span>
                    <div class="window-controls">
                        <div class="control-btn minimize"></div>
                        <div class="control-btn maximize"></div>
                        <div class="control-btn close" onclick="window.location.href='dashboard.php'"></div>
                    </div>
                </div>
                <div class="window-content">
                    <h2>Bank Queue System</h2>

                    <?php if ($step === "choose"): ?>
                        <form method="POST">
                            <label>Select Queueing Algorithm:</label>
                            <select name="algorithm" required>
                                <option value="">-- Choose --</option>
                                <option value="FIFO">FIFO (First In First Out)</option>
                                <option value="SJF">SJF (Shortest Job First)</option>
                                <option value="RR">Round Robin</option>
                            </select>
                            <input type="hidden" name="step" value="serve">
                            <button type="submit">Continue</button>
                        </form>

                    <?php elseif ($step === "serve" && $algorithm): ?>
                        <div class="algorithm-display">
                            Current Algorithm: <?= htmlspecialchars($algorithm) ?>
                        </div>

                        <form method="POST">
                            <input type="hidden" name="algorithm" value="<?= htmlspecialchars($algorithm) ?>">
                            <input type="hidden" name="step" value="serve">
                            <button type="submit">Serve Next Customer</button>
                        </form>

                        <h3>Current Queue</h3>

                        <?php
                        switch ($algorithm) {
                            case "SJF":
                                $order = "ORDER BY q.estimated_time ASC";
                                break;
                            case "RR":
                                $order = "ORDER BY q.queue_time ASC";
                                break;
                            default: 
                                $order = "ORDER BY q.queue_time ASC";
                        }

                        $queue_list = $conn->query("
                            SELECT q.*, t.type, t.amount, u.name 
                            FROM queue q
                            JOIN transactions t ON q.transaction_id = t.id
                            JOIN users u ON q.user_id = u.id
                            WHERE q.status = 'waiting'
                            $order
                        ");

                        if ($queue_list->num_rows > 0): ?>
                            <table>
                                <tr>
                                    <th>Position</th>
                                    <th>Name</th>
                                    <th>Transaction</th>
                                    <th>Amount</th>
                                    <th>Estimated Time</th>
                                    <th>Remaining Time</th>
                                    <th>Joined At</th>
                                </tr>
                                <?php $pos = 1; ?>
                                <?php while ($q = $queue_list->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $pos++ ?></td>
                                        <td><?= htmlspecialchars($q["name"]) ?></td>
                                        <td><?= $q["type"] ?></td>
                                        <td>₱<?= number_format($q["amount"], 2) ?></td>
                                        <td><?= $q["estimated_time"] ?> sec</td>
                                        <td><?= $q["remaining_time"] ?> sec</td>
                                        <td><?= $q["queue_time"] ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </table>
                        <?php else: ?>
                            <p class="no-customers">No customers currently in the queue.</p>
                        <?php endif; ?>

                        <?php
                        $result = $conn->query("
                            SELECT q.*, t.type, t.amount, t.id AS transaction_id, u.name, u.id AS customer_id, u.balance
                            FROM queue q
                            JOIN transactions t ON q.transaction_id = t.id
                            JOIN users u ON q.user_id = u.id
                            WHERE q.status = 'waiting'
                            $order
                        ");

                        $rows = [];
                        while ($row = $result->fetch_assoc()) {
                            $rows[] = $row;
                        }

                        if (count($rows) > 0) {
                            if ($algorithm === "RR") {
                                $index = $_SESSION['rr_index'] % count($rows);
                                $_SESSION['rr_index']++;
                                $row = $rows[$index];
                                $time_slice = 10; 
                                $wait_time = min($row['remaining_time'], $time_slice);
                            } else {
                                $row = $rows[0];
                                $wait_time = $row['estimated_time'];
                            }

                            $queue_id = $row["id"];
                            $transaction_id = $row["transaction_id"];
                            $customer_id = $row["customer_id"];
                            $customer_name = $row["name"];
                            $type = $row["type"];
                            $amount = $row["amount"];
                            $current_balance = $row["balance"];

                            $success = false;

                            if ($algorithm === "RR" && $row['remaining_time'] > 10) {
                                $new_remaining = $row['remaining_time'] - 10;
                                $conn->query("UPDATE queue SET remaining_time = $new_remaining WHERE id = $queue_id");
                            } else {
                                if ($type === "deposit") {
                                    $conn->query("UPDATE users SET balance = balance + $amount WHERE id = $customer_id");
                                    $success = true;
                                } elseif ($type === "withdrawal") {
                                    if ($current_balance >= $amount) {
                                        $conn->query("UPDATE users SET balance = balance - $amount WHERE id = $customer_id");
                                        $success = true;
                                    } else {
                                        echo "<div class='error-message'><strong>Failed:</strong> Insufficient balance for withdrawal by $customer_name.</div>";
                                    }
                                }

                                if ($success) {
                                    $conn->query("UPDATE transactions SET status = 'approved' WHERE id = $transaction_id");
                                    $conn->query("UPDATE queue SET status = 'served', teller_id = $teller_id WHERE id = $queue_id");

                                    $check = $conn->query("SELECT * FROM performance WHERE teller_id = $teller_id");
                                    if ($check->num_rows == 0) {
                                        $conn->query("INSERT INTO performance (teller_id, customers_served, total_wait_time) VALUES ($teller_id, 1, $wait_time)");
                                    } else {
                                        $conn->query("UPDATE performance SET customers_served = customers_served + 1, total_wait_time = total_wait_time + $wait_time WHERE teller_id = $teller_id");
                                    }
                                }
                            }

                            echo "<div class='processing-message'>
                                    <p><strong>Processing transaction for $customer_name...</strong></p>
                                    <p>Estimated time: $wait_time seconds</p>
                                </div>
                                <script>
                                    setTimeout(function() {
                                        document.getElementById('result').innerHTML = 
                                            '<div class=\"success-message\"><strong>Completed:</strong> $customer_name served ($type of ₱" . number_format($amount, 2) . ").</div>';
                                    }, " . ($wait_time * 1000) . ");
                                </script>
                                <div id='result'></div>";
                        } else {
                            echo "<p class='no-customers'>No customers to serve at this time.</p>";
                        }
                        ?>

                    <?php else: ?>
                        <div class="error-message">Error: Invalid request. Please go back and choose an algorithm.</div>
                    <?php endif; ?>
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