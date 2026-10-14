<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

if (isset($_POST['park'])) {

    $vehicle_id = $_POST['vehicle_id'];
    $slot_id = $_POST['slot_id'];

    $check = "SELECT id
              FROM parking_records
              WHERE vehicle_id = ?
              AND status = 'Parked'";

    $stmt = mysqli_prepare($conn, $check);

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $vehicle_id
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {

        $message = "This vehicle is already parked!";

    } else {

        $slot_check = "SELECT status
                       FROM parking_slots
                       WHERE id = ?";

        $stmt2 = mysqli_prepare($conn, $slot_check);

        mysqli_stmt_bind_param(
            $stmt2,
            "i",
            $slot_id
        );

        mysqli_stmt_execute($stmt2);

        $slot_result = mysqli_stmt_get_result($stmt2);

        $slot = mysqli_fetch_assoc($slot_result);

        if ($slot['status'] != 'Available') {

            $message = "Selected slot is not available!";

        } else {

            // Insert parking record
            $sql = "INSERT INTO parking_records
                    (user_id, vehicle_id, slot_id, entry_time, status, fee)
                    VALUES (?, ?, ?, NOW(), 'Parked', 0)";

            $stmt3 = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt3,
                "iii",
                $user_id,
                $vehicle_id,
                $slot_id
            );

            if (mysqli_stmt_execute($stmt3)) {

                // Update slot
                $update = "UPDATE parking_slots
                           SET status = 'Occupied'
                           WHERE id = ?";

                $stmt4 = mysqli_prepare($conn, $update);

                mysqli_stmt_bind_param(
                    $stmt4,
                    "i",
                    $slot_id
                );

                mysqli_stmt_execute($stmt4);

                $message = "Vehicle parked successfully!";

            } else {

                $message = "Parking failed!";
            }
        }
    }
}
?>

<html>
<head>
    <title>Park Vehicle</title>
</head>

<body>

<h2>Park Vehicle</h2>

<?php
if ($message != "") {
    echo "<p>" . htmlspecialchars($message) . "</p>";
}
?>

<form method="POST">

<label>Select Vehicle</label>
<br>

<select name="vehicle_id" required>

<option value="">Select Vehicle</option>

<?php

$sql = "SELECT id, vehicle_number, vehicle_type
        FROM vehicles
        WHERE user_id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {

?>

<option value="<?php echo $row['id']; ?>">

<?php
echo htmlspecialchars(
    $row['vehicle_number'] .
    " - " .
    $row['vehicle_type']
);
?>

</option>

<?php
}
?>

</select>

<br><br>

<label>Select Parking Slot</label>
<br>

<select name="slot_id" required>

<option value="">Select Slot</option>

<?php

$slots = mysqli_query(
    $conn,
    "SELECT id, slot_number
     FROM parking_slots
     WHERE status='Available'
     ORDER BY slot_number"
);

while ($slot = mysqli_fetch_assoc($slots)) {

?>

<option value="<?php echo $slot['id']; ?>">

<?php echo htmlspecialchars($slot['slot_number']); ?>

</option>

<?php
}
?>

</select>

<br><br>

<button type="submit" name="park">
Park Vehicle
</button>

</form>

<br>

<a href="dashboard.php">Back to Dashboard</a>

</body>
</html>