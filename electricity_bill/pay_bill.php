<?php
session_start();
include "db.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

if (!isset($_POST['bill_number'])) {
    die("Invalid request");
}

$bill_number = trim($_POST['bill_number']);

/* ===== UPDATE BILL STATUS ===== */
$update = mysqli_query($conn, "
    UPDATE bills
    SET status = 'PAID'
    WHERE bill_number = '$bill_number'
");

if (!$update) {
    die("SQL Error: " . mysqli_error($conn));
}

if (mysqli_affected_rows($conn) == 0) {
    die("Bill not found or already paid");
}

$_SESSION['msg'] = "✅ Payment successful. Bill marked as PAID.";

header("Location: user_dashboard.php");
exit();
