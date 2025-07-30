<?php
require_once "includes/db.php";
require_once "includes/auth.php";

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $algo = $_POST["algorithm"];
    $estimated_time = rand(3, 10); 
    $conn->query("INSERT INTO queue (user_id, estimated_time) VALUES ($user_id, $estimated_time)");
    echo "You’ve entered the queue using $algo algorithm.";
}
?>

<h2>Join Queue</h2>
<form method="POST">
    Choose Scheduling:
    <select name="algorithm">
        <option value="FIFO">FIFO</option>
        <option value="SJF">SJF</option>
        <option value="RR">Round Robin</option>
    </select><br>
    <button type="submit">Enter Queue</button>
</form>
<a href="dashboard.php">Back</a>
