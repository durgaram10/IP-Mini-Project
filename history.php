<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
?>

<html>
<head>
    <title>Parking History</title>
</head>

<body>

<h2>Parking History</h2>

<table border="1" cellpadding="10">

<tr>

<th>Vehicle Number</th>
<th>Vehicle Type</th>
<th>Slot</th>
<th>Entry Time</th>
<th>Exit Time</th>
<th>Status</th>
<th>Parking Fee</th>

</tr>

<?php

$sql = "SELECT
            vehicles.vehicle_number,
            vehicles.vehicle_type,
            parking_slots.slot_number,
            parking_records.entry_time,
            parking_records.exit_time,
            parking_records.status,
            parking_records.fee
        FROM parking_records
        JOIN vehicles
        ON parking_records.vehicle_id = vehicles.id
        JOIN parking_slots
        ON parking_records.slot_id = parking_slots.id
        WHERE parking_records.user_id = ?
        ORDER BY parking_records.id DESC";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {

    while ($row = mysqli_fetch_assoc($result)) {

?>

<tr>

<td>
<?php echo htmlspecialchars($row['vehicle_number']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['vehicle_type']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['slot_number']); ?>
</td>

<td>
<?php echo $row['entry_time']; ?>
</td>

<td>

<?php

if ($row['exit_time'] != NULL) {
    echo $row['exit_time'];
} else {
    echo "-";
}

?>

</td>

<td>
<?php echo htmlspecialchars($row['status']); ?>
</td>

<td>

<?php

if ($row['status'] == 'Parked') {

    echo "₹20.00";

} else {

    echo "₹" . number_format($row['fee'], 2);

}

?>

</td>

</tr>

<?php

    }

} else {

    echo "<tr>";
    echo "<td colspan='7'>No parking history found</td>";
    echo "</tr>";

}

?>

</table>

<br>

<a href="dashboard.php">Back to Dashboard</a>

</body>
</html>