<?php
require_once "includes/db.php";
require_once "includes/auth.php";

// Get current user
$user_id = $_SESSION["user_id"];

$result = $conn->query("SELECT type, amount, timestamp FROM transactions WHERE user_id = $user_id ORDER BY timestamp DESC");
?>

<h2>Transaction History</h2>

<?php if ($result->num_rows > 0): ?>
    <table border="1" cellpadding="8">
        <tr>
            <th>Type</th>
            <th>Amount (₱)</th>
            <th>Date & Time</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row["type"]) ?></td>
                <td><?= number_format($row["amount"], 2) ?></td>
                <td><?= $row["timestamp"] ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>No transactions found.</p>
<?php endif; ?>

<br>
<a href="dashboard.php">Back to Dashboard</a>
