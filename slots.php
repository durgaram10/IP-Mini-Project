<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$result = mysqli_query(
    $conn,
    "SELECT * FROM parking_slots ORDER BY slot_number"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Parking Slots</title>
</head>

<body>

<h2>Parking Slots</h2>

<table border="1" cellpadding="10">

<tr>
    <th>Slot Number</th>
    <th>Status</th>
</tr>

<?php

while ($row = mysqli_fetch_assoc($result)) {

?>

<tr>

<td>
<?php echo htmlspecialchars($row['slot_number']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['status']); ?>
</td>

</tr>

<?php
}
?>

</table>

<br>

<a href="dashboard.php">Back to Dashboard</a>

</body>
</html>