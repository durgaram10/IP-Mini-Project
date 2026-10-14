<?php

include "db.php";

$email = "HARSHAN5335@GMAIL.COM";
$new_password = "12345";

$hashed_password = password_hash(
    $new_password,
    PASSWORD_DEFAULT
);

$sql = "UPDATE users
        SET password = ?
        WHERE email = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ss",
    $hashed_password,
    $email
);

if (mysqli_stmt_execute($stmt)) {

    echo "Password reset successfully!";

} else {

    echo "Password reset failed!";
}

?>