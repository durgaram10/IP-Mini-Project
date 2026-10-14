
<?php
$conn = mysqli_connect("localhost", "root", "", "campusparking");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
