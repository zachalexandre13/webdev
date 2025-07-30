<?php
require_once "includes/db.php";
require_once "includes/auth.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = floatval($_POST["amount"]);
    $user_id = $_SESSION["user_id"];
    $conn->query("UPDATE users SET balance = balance + $amount WHERE id = $user_id");
    $conn->query("INSERT INTO transactions (user_id, type, amount) VALUES ($user_id, 'deposit', $amount)");
    header("Location: dashboard.php");
}
?>

<h2>Deposit</h2>
<form method="POST">
    Amount: <input type="number" name="amount" step="0.01" required><br>
    <button type="submit">Confirm Deposit</button>
</form>
<a href="dashboard.php">Back</a>
