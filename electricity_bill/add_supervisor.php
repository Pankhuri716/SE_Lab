<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "db.php";

if (!isset($_SESSION['admin'])) {
    header("Location: login.html");
    exit();
}

/* ===== HANDLE FORM SUBMISSION ===== */
if (isset($_POST['add'])) {

    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "
        SELECT id FROM supervisors WHERE username = '$username'
    ");

    if (mysqli_num_rows($check) > 0) {
        $_SESSION['msg'] = "Supervisor username already exists!";
        header("Location: admin_dashboard.php");
        exit();
    }

    $insert = mysqli_query($conn, "
        INSERT INTO supervisors (name, username, password)
        VALUES ('$name', '$username', '$password')
    ");

    if ($insert) {
        $_SESSION['msg'] = "Supervisor added successfully! Username: $username";
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $_SESSION['msg'] = "Failed to add supervisor!";
        header("Location: admin_dashboard.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Supervisor</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="form-container">

    <h2>Add Supervisor</h2>

    <!-- ✅ THIS IS WHERE THE FORM GOES -->
    <form method="post" action="add_supervisor.php">

        <input type="text" name="name" placeholder="Supervisor Name" required>

        <input type="text" name="username" placeholder="Username" required>

        <input type="password" name="password" placeholder="Password" required>

        <button type="submit" name="add">Add Supervisor</button>

    </form>

</div>

</body>
</html>
