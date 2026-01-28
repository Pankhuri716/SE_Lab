<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin'])) {
    header("Location: login.html");
    exit();
}

if (isset($_POST['add'])) {

    $name     = trim($_POST['name']);
    $phone    = trim($_POST['phone']);
    $address  = trim($_POST['address']);
    $pin      = trim($_POST['pin']);
    $category = $_POST['category'];

    /* ---------- SERVER-SIDE VALIDATION ---------- */

    // Name: only alphabets and spaces
    if (!preg_match("/^[A-Za-z ]+$/", $name)) {
        $_SESSION['msg'] = "Invalid Name! Only alphabets allowed.";
        header("Location: add_user.php");
        exit();
    }

    // Phone: exactly 10 digits
    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        $_SESSION['msg'] = "Invalid Phone Number! Must be exactly 10 digits.";
        header("Location: add_user.php");
        exit();
    }

    // Generate UNIQUE service number
    // Generate UNIQUE 10-digit numeric service number
do {
    $service = rand(1000000000, 2147483647); // first 10-digit safe for 32-bit PHP
    // If you need full 10 digits (max 9999999999), use string approach below
    $check = mysqli_query($conn, "SELECT service_number FROM users WHERE service_number='$service'");
} while (mysqli_num_rows($check) > 0);

    /* ---------- INSERT ---------- */

    $insert = mysqli_query($conn, "
        INSERT INTO users (name, phone, address, pin, category, service_number)
        VALUES ('$name','$phone','$address','$pin','$category','$service')
    ");

    if ($insert) {
        $_SESSION['msg'] = "User added successfully! Service Number: $service";
    } else {
        $_SESSION['msg'] = "Failed to add user!";
    }

    header("Location: add_user.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php
if (isset($_SESSION['msg'])) {
    echo "<script>alert('{$_SESSION['msg']}');</script>";
    unset($_SESSION['msg']);
}
?>

<form method="post" onsubmit="return validateForm()">
    <h2>Add User</h2>

    <!-- NAME -->
    <input type="text" name="name" id="name" placeholder="Name"
           oninput="validateName()" required>
    <small id="nameError" style="color:red;"></small>

    <!-- PHONE -->
    <input type="text" name="phone" id="phone" placeholder="Phone"
           oninput="validatePhone()" required>
    <small id="phoneError" style="color:red;"></small>

    <input type="text" name="address" placeholder="Address" required>
    <input type="text" name="pin" placeholder="PIN" required>

    <select name="category" required>
        <option value="">-- Select Category --</option>
        <option value="household">Household</option>
        <option value="commercial">Commercial</option>
        <option value="industry">Industry</option>
    </select>

    <button name="add">Add</button>
</form>

<script>
function validateName() {
    const name = document.getElementById("name").value;
    const error = document.getElementById("nameError");

    if (!/^[A-Za-z ]*$/.test(name)) {
        error.textContent = "Only alphabets allowed";
        return false;
    } else {
        error.textContent = "";
        return true;
    }
}

function validatePhone() {
    const phone = document.getElementById("phone").value;
    const error = document.getElementById("phoneError");

    if (!/^[0-9]*$/.test(phone)) {
        error.textContent = "Only digits allowed";
        return false;
    }

    if (phone.length > 0 && phone.length !== 10) {
        error.textContent = "Phone number must be 10 digits";
        return false;
    }

    error.textContent = "";
    return true;
}

function validateForm() {
    return validateName() && validatePhone();
}
</script>

</body>
</html>
