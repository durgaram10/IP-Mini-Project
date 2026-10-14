<?php
session_start();
require_once __DIR__ . "/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email == "" || $password == "") {
        $message = "Please enter email and password.";
    } else {

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, name, password FROM users WHERE email = ?"
        );

        if (!$stmt) {
            die("SQL Error: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user["password"])) {

            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["name"] = $user["name"];

            mysqli_stmt_close($stmt);

            header("Location: dashboard.php");
            exit;

        } else {
            $message = "Invalid email or password.";
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<html>
<head>
    <title>Campus Parking - Login</title>

    <style>
        body {
            font-family: Arial;
            text-align: center;
            background: #f2f2f2;
        }

        h1 {
            color: darkblue;
        }

        input {
            padding: 6px;
        }

        input[type="submit"] {
            background: lightblue;
            border: 1px solid blue;
            cursor: pointer;
        }
    </style>
</head>

<body>

<h1>Campus Parking System</h1>

<hr>

<h2>Student Login</h2>

<?php if ($message != ""): ?>
    <p><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="POST" action="">

    <p>Email</p>
    <input type="email" name="email" required>

    <p>Password</p>
    <input type="password" name="password" required>

    <p>
        <input type="submit" value="Login">
    </p>

</form>

<p>
    <a href="index.php">Back to Home</a>
</p>

<p>
    <a href="register.php">Create Account</a>
</p>

<hr>

<p>Campus Parking System - 2026</p>

</body>
</html>
