<?php
require_once "includes/db.php";
require_once "includes/auth.php";

// Restrict to tellers
if ($_SESSION["role"] !== "teller") {
    echo "Access denied.";
    exit();
}

$teller_id = $_SESSION["user_id"];
$step = $_POST["step"] ?? "choose"; // choose | serve
$algorithm = $_POST["algorithm"] ?? null;

// Round Robin state tracking
if (!isset($_SESSION['rr_index'])) {
    $_SESSION['rr_index'] = 0;
}
?>

<h2>Teller Panel – Bank Queue System</h2>

<?php if ($step === "choose"): ?>
    <form method="POST">
        <label>Select Queueing Algorithm:</label><br>
        <select name="algorithm" required>
            <option value="">-- Choose --</option>
            <option value="FIFO">FIFO (First In First Out)</option>
            <option value="SJF">SJF (Shortest Job First)</option>
            <option value="RR">Round Robin</option>
        </select>
        <br><br>
        <input type="hidden" name="step" value="serve">
        <button type="submit">Continue</button>
    </form>

<?php elseif ($step === "serve" && $algorithm): ?>

    <form method="POST">
        <p><strong>Current Algorithm:</strong> <?= htmlspecialchars($algorithm) ?></p>
        <input type="hidden" name="algorithm" value="<?= htmlspecialchars($algorithm) ?>">
        <input type="hidden" name="step" value="serve">
        <button type="submit">Serve Next Customer</button>
    </form>

    <br>

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

    echo "<h3>Current Queue (" . htmlspecialchars($algorithm) . ")</h3>";

    $queue_list = $conn->query("
        SELECT q.*, t.type, t.amount, u.name 
        FROM queue q
        JOIN transactions t ON q.transaction_id = t.id
        JOIN users u ON q.user_id = u.id
        WHERE q.status = 'waiting'
        $order
    ");

    if ($queue_list->num_rows > 0): ?>
        <table border="1" cellpadding="6">
            <tr>
                <th>Position</th>
                <th>Name</th>
                <th>Transaction</th>
                <th>Amount</th>
                <th>Estimated Time</th>
                <th>Joined At</th>
            </tr>
            <?php $pos = 1; ?>
            <?php while ($q = $queue_list->fetch_assoc()): ?>
                <tr>
                    <td><?= $pos++ ?></td>
                    <td><?= htmlspecialchars($q["name"]) ?></td>
                    <td><?= $q["type"] ?></td>
                    <td>₱<?= number_format($q["amount"], 2) ?></td>
                    <td><?= $q["estimated_time"] ?> min</td>
                    <td><?= $q["queue_time"] ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No customers currently in the queue.</p>
    <?php endif; ?>

    <br><br>

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
        } else {
            $row = $rows[0];
        }

        $queue_id = $row["id"];
        $transaction_id = $row["transaction_id"];
        $customer_id = $row["customer_id"];
        $customer_name = $row["name"];
        $type = $row["type"];
        $amount = $row["amount"];
        $current_balance = $row["balance"];

        $success = false;

        if ($type === "deposit") {
            $conn->query("UPDATE users SET balance = balance + $amount WHERE id = $customer_id");
            $success = true;
        } elseif ($type === "withdrawal") {
            if ($current_balance >= $amount) {
                $conn->query("UPDATE users SET balance = balance - $amount WHERE id = $customer_id");
                $success = true;
            } else {
                echo "<p style='color:red;'><strong>Failed:</strong> Insufficient balance for withdrawal by $customer_name.</p>";
            }
        }

        if ($success) {
            $conn->query("UPDATE transactions SET status = 'approved' WHERE id = $transaction_id");
            $conn->query("UPDATE queue SET status = 'served', teller_id = $teller_id WHERE id = $queue_id");

            $check = $conn->query("SELECT * FROM performance WHERE teller_id = $teller_id");
            if ($check->num_rows == 0) {
                $conn->query("INSERT INTO performance (teller_id, customers_served) VALUES ($teller_id, 1)");
            } else {
                $conn->query("UPDATE performance SET customers_served = customers_served + 1 WHERE teller_id = $teller_id");
            }

            $wait_seconds = rand(5, 15);

            echo "
                <p><strong>Processing transaction for $customer_name...</strong></p>
                <p>Estimated time: $wait_seconds seconds</p>
                <script>
                    setTimeout(function() {
                        document.getElementById('result').innerHTML = 
                            '<p style=\"color:green;\"><strong>Success:</strong> Served $customer_name. $type of ₱$amount processed.</p>';
                    }, " . ($wait_seconds * 1000) . ");
                </script>
                <div id='result'></div>
            ";
        }
    } else {
        echo "<p>No customers to serve at this time.</p>";
    }
    ?>

<?php else: ?>
    <p>Error: Invalid request. Please go back and choose an algorithm.</p>
<?php endif; ?>

<br>
<a href="dashboard.php">Back to Dashboard</a>
