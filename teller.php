<?php
require_once "includes/db.php";
require_once "includes/auth.php";

$teller_id = $_SESSION["user_id"];
$algo = "FIFO"; 

switch ($algo) {
    case "SJF":
        $order = "ORDER BY estimated_time ASC";
        break;
    case "RR":
        $order = "ORDER BY queue_time ASC"; 
        break;
    default:
        $order = "ORDER BY queue_time ASC";
}

$next = $conn->query("SELECT * FROM queue WHERE status = 'waiting' $order LIMIT 1");

if ($next->num_rows > 0) {
    $client = $next->fetch_assoc();
    $queue_id = $client["id"];
    $conn->query("UPDATE queue SET status = 'served', teller_id = $teller_id WHERE id = $queue_id");
    $conn->query("UPDATE performance SET customers_served = customers_served + 1 WHERE teller_id = $teller_id");
    echo "You have served customer ID " . $client["user_id"];
} else {
    echo "No customers in queue.";
}
?>

<a href="dashboard.php">Back</a>
