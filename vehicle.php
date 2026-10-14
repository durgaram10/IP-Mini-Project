<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

if (isset($_POST['register'])) {

    $vehicle_number = strtoupper(trim($_POST['vehicle_number']));
    $vehicle_type = $_POST['vehicle_type'];

    $check = "SELECT id
              FROM vehicles
              WHERE vehicle_number = ?";

    $stmt = mysqli_prepare($conn, $check);

    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $vehicle_number
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {

        $message = "Vehicle number already registered!";

    } else {

        $sql = "INSERT INTO vehicles
                (user_id, vehicle_number, vehicle_type)
                VALUES (?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "iss",
            $user_id,
            $vehicle_number,
            $vehicle_type
        );

        if (mysqli_stmt_execute($stmt)) {

            $message = "Vehicle registered successfully!";

        } else {

            $message = "Vehicle registration failed!";
        }
    }

    mysqli_stmt_close($stmt);
}
?>

<html>
<head>
    <title>Vehicle Registration</title>
</head>

<body>

<h2>Vehicle Registration</h2>

<?php
if ($message != "") {
    echo "<p>" . htmlspecialchars($message) . "</p>";
}
?>

<form method="POST">

    <label>Vehicle Number</label>
    <br>
    <input
        type="text"
        name="vehicle_number"
        placeholder="TN59AB1234"
        required
    >

    <br><br>

    <label>Vehicle Type</label>
    <br>

    <select name="vehicle_type" required>

        <option value="">Select Vehicle</option>
        <option value="Bike">Bike</option>
        <option value="Car">Car</option>
        <option value="Scooter">Scooter</option>

    </select>

    <br><br>

    <button type="submit" name="register">
        Register Vehicle
    </button>

</form>

<br>

<a href="dashboard.php">Back to Dashboard</a>

</body>
</html>