<?php
session_start();
include "db.php";
if(!isset($_SESSION['admin'])) header("Location: login.html");

$q = mysqli_query($conn,"
SELECT
    b.bill_number,
    b.service_number,
    u.name AS username,
    m.previous_reading,
    m.current_reading,
    b.basic_charge,
    b.rate,
    b.total,
    b.bill_date,
    b.due_date,
    b.due_date_fine,
    b.status
FROM bills b
JOIN users u ON u.service_number = b.service_number
JOIN meter_readings m ON m.id = b.meter_reading_id
ORDER BY b.bill_date DESC
");


?>
<!DOCTYPE html>
<html>
<head>
<title>All Bills</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="dashboard-container">

<h1>All User Bills</h1>
<p class="welcome">Admin View</p>

<div class="table-box" style="overflow-x:auto;">

<table>
<tr>
    <th>Bill No</th>
    <th>Service No</th>
    <th>User Name</th>
    <th>Previous</th>
    <th>Current</th>
    <th>Basic ₹</th>
    <th>Rate ₹</th>
    <th>Total ₹</th>
    <th>Bill Date</th>
    <th>Due (No Fine)</th>
    <th>Due (With Fine)</th>
    <th>Status</th>
</tr>

<?php while($b=mysqli_fetch_assoc($q)){ ?>
<tr>
    <td><?= $b['bill_number'] ?></td>
    <td><?= $b['service_number'] ?></td>
    <td><?= $b['username'] ?></td>
    <td><?= $b['previous_reading'] ?></td>
    <td><?= $b['current_reading'] ?></td>
    <td><?= $b['basic_charge'] ?></td>
    <td><?= $b['rate'] ?></td>
    <td><strong><?= $b['total'] ?></strong></td>
    <td><?= $b['bill_date'] ?></td>
    <td><?= $b['due_date'] ?></td>
    <td><?= $b['due_date_fine'] ?></td>
    <td class="<?= $b['status']=="PAID" ? "status-paid" : "status-unpaid" ?>">
        <?= $b['status'] ?>
    </td>
</tr>
<?php } ?>

</table>

</div>

<br>

<div class="card-container">
    <a href="admin_dashboard.php" class="card">
        <h3>Back</h3>
        <p>Return to dashboard</p>
    </a>

    <a href="logout.php" class="card logout">
        <h3>Logout</h3>
        <p>Exit Admin panel</p>
    </a>
</div>

</div>

</body>
</html>
