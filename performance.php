<?php
require_once "includes/db.php";
require_once "includes/auth.php";

$res = $conn->query("SELECT u.name, p.customers_served, p.avg_wait_time
                    FROM performance p JOIN users u ON u.id = p.teller_id");

echo "<h2>Teller Performance</h2>";
while ($row = $res->fetch_assoc()) {
    echo "Teller: " . $row['name'] . "<br>";
    echo "Served: " . $row['customers_served'] . "<br>";
    echo "Avg Wait: " . $row['avg_wait_time'] . "<br><hr>";
}
?>
<br>
<a href="dashboard.php">Back</a>Z
