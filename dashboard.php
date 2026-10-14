<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION["user_id"];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $slot = $_POST["slot_number"] ?? "";
    $action = $_POST["action"] ?? "";

    if ($action == "book") {
        mysqli_begin_transaction($conn);

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE parking_slots SET status='Occupied'
             WHERE slot_number=? AND status='Available'"
        );
        mysqli_stmt_bind_param($stmt, "s", $slot);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) == 1) {
            $stmt2 = mysqli_prepare(
                $conn,
                "INSERT INTO parking_history (user_id, slot_number)
                 VALUES (?, ?)"
            );
            mysqli_stmt_bind_param($stmt2, "is", $user_id, $slot);

            if (mysqli_stmt_execute($stmt2)) {
                mysqli_commit($conn);
                $message = "Parking slot booked successfully!";
            } else {
                mysqli_rollback($conn);
                $message = "Booking failed.";
            }
            mysqli_stmt_close($stmt2);
        } else {
            mysqli_rollback($conn);
            $message = "This slot is not available.";
        }

        mysqli_stmt_close($stmt);
    }

    if ($action == "release") {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id FROM parking_history
             WHERE user_id=? AND slot_number=? AND exit_time IS NULL
             ORDER BY id DESC LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "is", $user_id, $slot);
        mysqli_stmt_execute($stmt);
        $active = mysqli_stmt_get_result($stmt);
        $record = mysqli_fetch_assoc($active);
        mysqli_stmt_close($stmt);

        if ($record) {
            mysqli_begin_transaction($conn);

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE parking_history SET exit_time=NOW()
                 WHERE id=? AND exit_time IS NULL"
            );
            mysqli_stmt_bind_param($stmt, "i", $record["id"]);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE parking_slots SET status='Available'
                 WHERE slot_number=?"
            );
            mysqli_stmt_bind_param($stmt, "s", $slot);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            mysqli_commit($conn);
            $message = "Parking slot released successfully!";
        } else {
            $message = "You have not booked this slot.";
        }
    }
}

$slots = mysqli_query($conn, "SELECT * FROM parking_slots ORDER BY slot_number");

$total_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM parking_slots");
$total = mysqli_fetch_assoc($total_result)["total"];

$available_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM parking_slots WHERE status='Available'"
);
$available = mysqli_fetch_assoc($available_result)["total"];

$occupied = $total - $available;

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM parking_history
     WHERE user_id=? ORDER BY entry_time DESC"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$history = mysqli_stmt_get_result($stmt);
?>

<html>
<head>
    <title>Campus Parking Dashboard</title>
</head>
<body>
<center>
    <h1>Campus Parking System</h1>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?></p>
    <hr>

    <h2>Parking Dashboard</h2>
    <p>Total Slots: <?php echo $total; ?></p>
    <p>Available Slots: <?php echo $available; ?></p>
    <p>Occupied Slots: <?php echo $occupied; ?></p>

    <p><?php echo htmlspecialchars($message); ?></p>

    <h2>Parking Slots</h2>

    <table border="1" cellpadding="8">
        <tr>
            <th>Slot Number</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($slots)) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row["slot_number"]); ?></td>
            <td><?php echo htmlspecialchars($row["status"]); ?></td>
            <td>
                <?php if ($row["status"] == "Available") { ?>
                    <form method="post">
                        <input type="hidden" name="slot_number"
                               value="<?php echo htmlspecialchars($row["slot_number"]); ?>">
                        <button type="submit" name="action" value="book">
                            Book Slot
                        </button>
                    </form>
                <?php } else { ?>
                    <?php
                    $check = mysqli_prepare(
                        $conn,
                        "SELECT id FROM parking_history
                         WHERE user_id=? AND slot_number=?
                         AND exit_time IS NULL LIMIT 1"
                    );
                    mysqli_stmt_bind_param(
                        $check, "is", $user_id, $row["slot_number"]
                    );
                    mysqli_stmt_execute($check);
                    $my_booking = mysqli_stmt_get_result($check);
                    $is_mine = mysqli_num_rows($my_booking) > 0;
                    mysqli_stmt_close($check);
                    ?>

                    <?php if ($is_mine) { ?>
                        <form method="post">
                            <input type="hidden" name="slot_number"
                                   value="<?php echo htmlspecialchars($row["slot_number"]); ?>">
                            <button type="submit" name="action" value="release">
                                Release Slot
                            </button>
                        </form>
                    <?php } else { ?>
                        Occupied
                    <?php } ?>
                <?php } ?>
            </td>
        </tr>
        <?php } ?>
    </table>

    <h2>My Parking History</h2>

    <table border="1" cellpadding="8">
        <tr>
            <th>Slot Number</th>
            <th>Entry Time</th>
            <th>Exit Time</th>
            <th>Status</th>
        </tr>

        <?php if (mysqli_num_rows($history) > 0) { ?>
            <?php while ($row = mysqli_fetch_assoc($history)) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row["slot_number"]); ?></td>
                <td><?php echo htmlspecialchars($row["entry_time"]); ?></td>
                <td>
                    <?php echo $row["exit_time"]
                        ? htmlspecialchars($row["exit_time"])
                        : "-"; ?>
                </td>
                <td>
                    <?php echo $row["exit_time"] ? "Completed" : "Parked"; ?>
                </td>
            </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="4">No parking history available.</td>
            </tr>
        <?php } ?>
    </table>

    <br>
    <a href="logout.php">Logout</a>
</center>
</body>
</html>
```