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

$result = mysqli_query(
    $conn,
    "SELECT id, name, email, role
     FROM users
     ORDER BY id DESC"
);
?>

<html>
<head>
    <title>Users</title>
</head>

<body>

<h2>Registered Users</h2>

<table border="1" cellpadding="10">

<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Role</th>
</tr>

<?php

while ($row = mysqli_fetch_assoc($result)) {

?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo htmlspecialchars($row['name']); ?></td>

<td><?php echo htmlspecialchars($row['email']); ?></td>

<td><?php echo htmlspecialchars($row['role']); ?></td>

</tr>

<?php
}
?>

</table>

<br>

<a href="admin.php">Back to Admin Dashboard</a>

</body>
</html>