<?php
session_start();
include "db.php";

/* ===== AUTH CHECK ===== */
if (!isset($_SESSION['supervisor'])) {
    header("Location: login.html");
    exit();
}

/* ===== METHOD CHECK ===== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid access");
}

/* ===== ACCEPT BOTH OLD & NEW FORM FIELD NAMES ===== */
$service_number =
    $_POST['service_number']
    ?? $_POST['service']
    ?? '';

$previous =
    $_POST['previous_reading']
    ?? $_POST['previous']
    ?? '';

$current =
    $_POST['current_reading']
    ?? $_POST['current']
    ?? '';

/* ===== VALIDATION ===== */
if ($service_number === '' || $previous === '' || $current === '') {
    $_SESSION['msg'] = "Missing form data";
    header("Location: supervisor_dashboard.php");
    exit();
}

if (!is_numeric($previous) || !is_numeric($current) || $current < $previous) {
    $_SESSION['msg'] = "Invalid meter readings";
    header("Location: supervisor_dashboard.php");
    exit();
}

/* ===== BILL CALCULATION ===== */
$units = $current - $previous;

/* ===== Tiered Rate Calculation ===== */
$total = 0;
$remaining = $units;

if ($remaining > 0) {
    // First 50 units
    $first = min($remaining, 50);
    $total += $first * 1.5;
    $remaining -= $first;

    // Second 50 units
    if ($remaining > 0) {
        $second = min($remaining, 50);
        $total += $second * 2.5;
        $remaining -= $second;
    }

    // Third 50 units
    if ($remaining > 0) {
        $third = min($remaining, 50);
        $total += $third * 3.5;
        $remaining -= $third;
    }

    // Units above 150
    if ($remaining > 0) {
        $total += $remaining * 4.5;
    }
} else {
    // Minimum charge if 0 units
    $total = 25;
}

/* Basic charge is already included in above calculation if needed,
   else you can add extra basic_charge like before. */
$basic_charge = 0; // already counted in tiered rates
$bill_number = "BILL" . rand(10000, 99999);
$fine_amount = 150; // Fine after due date

/* ===== TRANSACTION START ===== */
mysqli_begin_transaction($conn);

try {

    /* ===== INSERT METER READING ===== */
    $q1 = mysqli_query($conn, "
        INSERT INTO meter_readings (
            service_number,
            previous_reading,
            current_reading,
            reading_date
        ) VALUES (
            '$service_number',
            $previous,
            $current,
            CURDATE()
        )
    ");

    if (!$q1) throw new Exception("Failed to insert meter reading");

    $meter_reading_id = mysqli_insert_id($conn);

    /* ===== INSERT BILL ===== */
    $q2 = mysqli_query($conn, "
        INSERT INTO bills (
            bill_number,
            service_number,
            meter_reading_id,
            basic_charge,
            rate,
            units,
            total,
            bill_date,
            due_date,
            due_date_fine,
            status
        ) VALUES (
            '$bill_number',
            '$service_number',
            $meter_reading_id,
            $basic_charge,
            0,  /* rate not used since we have tiered calculation */
            $units,
            $total,
            CURDATE(),
            DATE_ADD(CURDATE(), INTERVAL 15 DAY),
            DATE_ADD(CURDATE(), INTERVAL 25 DAY),
            'UNPAID'
        )
    ");

    if (!$q2) throw new Exception("Failed to insert bill");

    mysqli_commit($conn);

    /* SUCCESS MESSAGE */
    $_SESSION['msg'] = "Bill generated successfully! Bill Number: $bill_number Total: ₹$total";

    header("Location: supervisor_dashboard.php");
    exit();

} catch (Exception $e) {

    mysqli_rollback($conn);
    $_SESSION['msg'] = "Error generating bill: " . $e->getMessage();
    header("Location: supervisor_dashboard.php");
    exit();
}
?>
