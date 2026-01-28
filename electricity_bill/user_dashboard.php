<?php
session_start();
include "db.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

$service_number = $_SESSION['user'];

/* ===== FETCH LATEST BILL (UNPAID FIRST, ELSE PAID) ===== */
$latest_sql = "
    SELECT 
        b.*, 
        u.name AS username, 
        u.address, 
        u.phone, 
        u.pin,
        m.previous_reading, 
        m.current_reading
    FROM bills b
    JOIN users u ON b.service_number = u.service_number
    JOIN meter_readings m ON b.meter_reading_id = m.id
    WHERE b.service_number = '$service_number'
    ORDER BY 
        CASE WHEN b.status = 'UNPAID' THEN 0 ELSE 1 END,
        b.bill_date DESC,
        b.id DESC
    LIMIT 1
";

$latest_res = mysqli_query($conn, $latest_sql);
$bill = mysqli_fetch_assoc($latest_res);

/* ===== PENDING AMOUNT (OTHER UNPAID BILLS) ===== */
$pending_charges = 0;
if ($bill) {
    $pending_sql = "
        SELECT IFNULL(SUM(total),0) AS pending_total
        FROM bills
        WHERE service_number = '$service_number'
        AND status = 'UNPAID'
        AND bill_number != '{$bill['bill_number']}'
    ";
    $pending_res = mysqli_query($conn, $pending_sql);
    $pending_row = mysqli_fetch_assoc($pending_res);
    $pending_charges = $pending_row['pending_total'];
}

/* ===== PAYABLE AMOUNT ===== */
$today = date('Y-m-d');
$fine = 150;

$total_payable = 0;
$payable_with_fine = 0;

if ($bill) {
    $total_payable = $bill['total'] + $pending_charges;

    if ($bill['status'] === 'UNPAID' && $today > $bill['due_date']) {
        $payable_with_fine = $total_payable + $fine;
    } else {
        $payable_with_fine = $total_payable;
    }
}

/* ===== BILL HISTORY ===== */
$history = mysqli_query($conn, "
    SELECT bill_number, bill_date, total, status
    FROM bills
    WHERE service_number = '$service_number'
    ORDER BY bill_date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <link rel="stylesheet" href="/electricity_bill/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f5f5f5;
        }

        .container {
            width: 90%;
            margin: 20px auto;
        }

        .bill-box, .history-box {
            border: 1px solid #ccc;
            padding: 20px;
            background: #fff;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        h1, h2, h3 {
            margin-top: 0;
        }

        .pay-btn {
            padding: 10px 20px;
            background: green;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #eee;
            padding: 8px;
            text-align: center;
        }

        th {
            background: #eee;
            color: black;
        }

        .unpaid {
            color: red;
            font-weight: bold;
        }

        .paid {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">

    <!-- ===== CURRENT BILL ===== -->
    <div class="bill-box">
        <h2>My Current Electricity Bill</h2>

        <?php if ($bill) { ?>
            <h2>STATE ELECTRICITY BOARD</h2>

            <p><b>Bill No:</b> <?= $bill['bill_number'] ?></p>
            <p><b>Bill Date:</b> <?= $bill['bill_date'] ?></p>

            <h3>Consumer Details</h3>
            <p>Name: <?= $bill['username'] ?></p>
            <p>Service No: <?= $bill['service_number'] ?></p>
            <p>Address: <?= $bill['address'] ?> - <?= $bill['pin'] ?></p>
            <p>Phone: <?= $bill['phone'] ?></p>

            <h3>Meter Reading</h3>
            <p>Previous: <?= $bill['previous_reading'] ?></p>
            <p>Current: <?= $bill['current_reading'] ?></p>
            <p>Units Consumed: <?= $bill['units'] ?></p>

            <h3>Charges</h3>
            <p>Current Bill Amount: ₹<?= $bill['total'] ?></p>

            <?php if ($pending_charges > 0) { ?>
                <p>Previous Pending Amount: ₹<?= $pending_charges ?></p>
            <?php } ?>

            <p><b>Due Date (No Fine):</b> <?= $bill['due_date'] ?></p>
            <p><b>Due Date (With Fine):</b> <?= $bill['due_date_fine'] ?></p>
            <p><b>Fine:</b> ₹150</p>

            <hr>
            <p><b>Total Payable:</b> ₹<?= $total_payable ?></p>
            <p><b>Payable Now:</b> ₹<?= $payable_with_fine ?></p>
            <p><b>Status:</b> 
                <?php if($bill['status'] === 'UNPAID'){ echo '<span class="unpaid">UNPAID</span>'; } else { echo '<span class="paid">PAID</span>'; } ?>
            </p>

            <?php if ($bill['status'] === 'UNPAID') { ?>
                <form method="post" action="pay_bill.php">
                    <input type="hidden" name="bill_number" value="<?= $bill['bill_number'] ?>">
                    <button type="submit" class="pay-btn">Pay Bill</button>
                </form>
            <?php } else { ?>
                <p style="color:green;font-weight:bold;">✔ Bill Paid</p>
            <?php } ?>

        <?php } else { ?>
            <p style="color:red;font-weight:bold;">No bills generated yet.</p>
        <?php } ?>
    </div>

    <!-- ===== PAST BILLS ===== -->
    <div class="history-box">
        <h2>Past Bills</h2>

        <table>
            <tr>
                <th>Bill No</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Status</th>
            </tr>

            <?php while ($h = mysqli_fetch_assoc($history)) { ?>
                <tr>
                    <td><?= $h['bill_number'] ?></td>
                    <td><?= $h['bill_date'] ?></td>
                    <td>₹<?= $h['total'] ?></td>
                    <td class="<?= $h['status'] === 'UNPAID' ? 'unpaid' : 'paid' ?>"><?= $h['status'] ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>

</div>

</body>
</html>
