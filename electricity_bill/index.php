<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.html");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Electricity Bill</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h2>Electricity Bill Generator</h2>
<a href="logout.php">Logout</a>

<form action="generate_bill.php" method="post" onsubmit="return validateForm()">

    <label>Meter ID</label>
    <input type="text" name="meter_id" required>

    <label>Name</label>
    <input type="text" name="name" required>

    <label>Phone Number</label>
    <input type="text" name="phone" id="phone" required>

    <label>Address</label>
    <textarea name="address" required></textarea>

    <label>PIN Code</label>
    <input type="text" name="pin" id="pin" required>

    <label>Service Category</label>
    <select name="category" required>
        <option value="household">Household</option>
        <option value="commercial">Commercial</option>
        <option value="industry">Industry</option>
    </select>

    <label>Previous Meter Reading</label>
    <input type="number" name="previous_reading" required>

    <label>Current Meter Reading</label>
    <input type="number" name="current_reading" required>

    <button type="submit">Generate Bill</button>
</form>

<script>
function validateForm() {
    let phone = document.getElementById("phone").value;
    let pin = document.getElementById("pin").value;
    let prev = document.getElementsByName("previous_reading")[0].value;
    let curr = document.getElementsByName("current_reading")[0].value;

    if (!/^[0-9]{10}$/.test(phone)) {
        alert("Phone number must be 10 digits");
        return false;
    }

    if (!/^[0-9]{6}$/.test(pin)) {
        alert("PIN must be 6 digits");
        return false;
    }

    if (parseInt(curr) < parseInt(prev)) {
        alert("Current reading cannot be less than previous reading");
        return false;
    }
    return true;
}
</script>

</body>
</html>
