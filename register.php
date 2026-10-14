<?php
include "db.php";

$message = "";

if (isset($_POST['register'])) {

    $name = trim($_POST['name']);
    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password != $confirm_password) {

        $message = "Passwords do not match!";

    } else {

        $check_sql = "SELECT id FROM users WHERE email = ?";

        $stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {

            $message = "Email already registered!";

        } else {

            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $sql = "INSERT INTO users
                    (name, email, password, role)
                    VALUES (?, ?, ?, 'student')";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "sss",
                $name,
                $email,
                $hashed_password
            );

            if (mysqli_stmt_execute($stmt)) {

                $message = "Registration successful! You can login now.";

            } else {

                $message = "Registration failed!";
            }
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<html>
<head>
    <title>Register</title>
</head>

<body>

<h2>Student Registration</h2>

<?php
if ($message != "") {
    echo "<p>" . htmlspecialchars($message) . "</p>";
}
?>

<form method="POST">

    <label>Name</label>
    <br>
    <input type="text" name="name" required>

    <br><br>

    <label>Email</label>
    <br>
    <input type="email" name="email" required>

    <br><br>

    <label>Password</label>
    <br>
    <input type="password" name="password" required>

    <br><br>

    <label>Confirm Password</label>
    <br>
    <input type="password" name="confirm_password" required>

    <br><br>

    <button type="submit" name="register">
        Register
    </button>

</form>

<br>

<a href="login.php">Already have an account? Login</a>

</body>
</html>