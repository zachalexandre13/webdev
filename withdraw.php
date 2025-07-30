<?php
require_once "includes/db.php";
require_once "includes/auth.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = floatval($_POST["amount"]);
    $user_id = $_SESSION["user_id"];

    $res = $conn->query("SELECT balance FROM users WHERE id = $user_id");
    $bal = $res->fetch_assoc()["balance"];

    if ($bal >= $amount) {
        $conn->query("UPDATE users SET balance = balance - $amount WHERE id = $user_id");
        $conn->query("INSERT INTO transactions (user_id, type, amount) VALUES ($user_id, 'withdrawal', $amount)");
        header("Location: dashboard.php");
    } else {
        echo "Insufficient balance.";
    }
}
?>

<h2>Withdraw</h2>
<form method="POST">
    Amount: <input type="number" name="amount" step="0.01" required><br>
    <button type="submit">Confirm Withdrawal</button>
</form>
<a href="dashboard.php">Back</a>
