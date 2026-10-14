<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

if (isset($_POST['exit_vehicle'])) {

    $record_id = $_POST['record_id'];

    $sql = "SELECT entry_time, slot_id
            FROM parking_records
            WHERE id = ?
            AND user_id = ?
            AND status = 'Parked'";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $record_id,
        $user_id
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $record = mysqli_fetch_assoc($result);

    if ($record) {

        $entry_time = strtotime($record['entry_time']);
        $exit_time = time();

        $duration_seconds = $exit_time - $entry_time;

        $hours = ceil($duration_seconds / 3600);

        if ($hours < 1) {
            $hours = 1;
        }

        $fee_result = mysqli_query(
            $conn,
            "SELECT hourly_fee
             FROM parking_settings
             WHERE id = 1"
        );

        if ($fee_result && mysqli_num_rows($fee_result) > 0) {

            $fee_data = mysqli_fetch_assoc($fee_result);

            $hourly_fee = $fee_data['hourly_fee'];

        } else {

            $hourly_fee = 20;
        }

        $fee = $hours * $hourly_fee;

        $update = "UPDATE parking_records
                   SET exit_time = NOW(),
                       status = 'Completed',
                       fee = ?
                   WHERE id = ?";

        $stmt2 = mysqli_prepare($conn, $update);

        mysqli_stmt_bind_param(
            $stmt2,
            "di",
            $fee,
            $record_id
        );

        mysqli_stmt_execute($stmt2);

        $slot_id = $record['slot_id'];

        $slot_update = "UPDATE parking_slots
                        SET status = 'Available'
                        WHERE id = ?";

        $stmt3 = mysqli_prepare($conn, $slot_update);

        mysqli_stmt_bind_param(
            $stmt3,
            "i",
            $slot_id
        );

        mysqli_stmt_execute($stmt3);

        $message =
            "Vehicle exited successfully! " .
            "Parking Fee: ₹" .
            number_format($fee, 2);

    } else {

        $message = "Invalid parking record!";
    }
}
?>

<html>
<head>
    <title>Vehicle Exit</title>
</head>

<body>

<h2>Vehicle Exit</h2>

<?php
if ($message != "") {
    echo "<p>" . htmlspecialchars($message) . "</p>";
}
?>

<table border="1" cellpadding="10">

<tr>
    <th>Vehicle Number</th>
    <th>Vehicle Type</th>
    <th>Slot</th>
    <th>Entry Time</th>
    <th>Action</th>
</tr>

<?php

$sql = "SELECT
            parking_records.id,
            parking_records.entry_time,
            vehicles.vehicle_number,
            vehicles.vehicle_type,
            parking_slots.slot_number
        FROM parking_records
        JOIN vehicles
        ON parking_records.vehicle_id = vehicles.id
        JOIN parking_slots
        ON parking_records.slot_id = parking_slots.id
        WHERE parking_records.user_id = ?
        AND parking_records.status = 'Parked'";

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

<form method="POST">

<input
    type="hidden"
    name="record_id"
    value="<?php echo $row['id']; ?>"
>

<button type="submit" name="exit_vehicle">
Exit Vehicle
</button>

</form>

</td>

</tr>

<?php

    }

} else {

    echo "<tr>";
    echo "<td colspan='5'>";
    echo "No vehicle currently parked";
    echo "</td>";
    echo "</tr>";
}

?>

</table>

<br>

<a href="dashboard.php">Back to Dashboard</a>

</body>
</html>