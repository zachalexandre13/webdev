<?php
require_once "includes/db.php";
require_once "includes/auth.php";

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $result->fetch_assoc();
?>

<h2>Welcome, <?= htmlspecialchars($user['name']) ?>!</h2>
<p>Account No: <?= $user['account_no'] ?></p>
<p>Balance: ₱<?= number_format($user['balance'], 2) ?></p>

<?php if ($_SESSION["role"] === "customer"): ?>
    <a href="deposit.php">Deposit</a><br>
    <a href="withdraw.php">Withdraw</a><br>
    <a href="transactions.php">View Transactions</a><br>
<?php endif; ?>

<?php if ($_SESSION["role"] === "teller"): ?>
    <a href="teller.php">Teller Panel</a><br>
    <a href="performance.php">Performance Report</a><br>
<?php endif; ?>

<a href="logout.php">Logout</a>

