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
                  VALUES ($user_id, 'withdrawal', $amount)");
    $transaction_id = $conn->insert_id;

    $check = $conn->query("SELECT * FROM queue WHERE user_id = $user_id AND status = 'waiting'");
    if ($check->num_rows == 0) {
        $estimated_time = rand(3, 10);
        $conn->query("INSERT INTO queue (user_id, transaction_id, estimated_time) 
                      VALUES ($user_id, $transaction_id, $estimated_time)");
    }

    echo "<p>Withdrawal request submitted and added to queue.</p>";
    echo "<a href='dashboard.php'>Return to Dashboard</a>";
    exit();
}
?>

<h2>Withdraw</h2>
<form method="POST">
    Amount: <input type="number" name="amount" step="0.01" required><br>
    <button type="submit">Submit Withdrawal Request</button>
</form>
