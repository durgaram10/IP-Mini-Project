<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] != 'admin') {
    echo "Access Denied!";
    exit();
}

$search = isset($_GET['search'])
    ? mysqli_real_escape_string($conn, $_GET['search'])
    : "";

$status = isset($_GET['status'])
    ? mysqli_real_escape_string($conn, $_GET['status'])
    : "";

$sql = "SELECT
            users.name,
            users.email,
            vehicles.vehicle_number,
            vehicles.vehicle_type,
            parking_slots.slot_number,
            parking_records.entry_time,
            parking_records.exit_time,
            parking_records.status,
            parking_records.fee
        FROM parking_records
        JOIN users
        ON parking_records.user_id = users.id
        JOIN vehicles
        ON parking_records.vehicle_id = vehicles.id
        JOIN parking_slots
        ON parking_records.slot_id = parking_slots.id
        WHERE 1=1";

if ($search != "") {

    $sql .= " AND (
        users.name LIKE '%$search%'
        OR vehicles.vehicle_number LIKE '%$search%'
        OR users.email LIKE '%$search%'
    )";
}

if ($status != "") {

    $sql .= " AND parking_records.status = '$status'";
}

$sql .= " ORDER BY parking_records.id DESC";

$result = mysqli_query($conn, $sql);
?>

<html>
<head>
    <title>Admin Parking History</title>
</head>

<body>

<h2>All Parking History</h2>

<form method="GET">

<input
    type="text"
    name="search"
    placeholder="Search student or vehicle"
    value="<?php echo htmlspecialchars($search); ?>"
>

<select name="status">

<option value="">All Status</option>

<option
    value="Parked"
    <?php if ($status == "Parked") echo "selected"; ?>
>
Parked
</option>

<option
    value="Completed"
    <?php if ($status == "Completed") echo "selected"; ?>
>
Completed
</option>

</select>

<button type="submit">
Search
</button>

</form>

<br>

<table border="1" cellpadding="10">

<tr>

<th>Student</th>
<th>Email</th>
<th>Vehicle</th>
<th>Type</th>
<th>Slot</th>
<th>Entry</th>
<th>Exit</th>
<th>Status</th>
<th>Fee</th>

</tr>

<?php

while ($row = mysqli_fetch_assoc($result)) {

?>

<tr>

<td>
<?php echo htmlspecialchars($row['name']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['email']); ?>
</td>

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
echo $row['exit_time']
    ? $row['exit_time']
    : "-";
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
?>

</table>

<br>

<a href="admin.php">Back to Admin Dashboard</a>

</body>
</html>