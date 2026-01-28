<?php
session_start();
include "db.php";

/* ===== HANDLE FORM SUBMISSION ===== */
$bills = [];
$error = "";

if (isset($_POST['view'])) {

    $service = trim($_POST['service']);

    // Validate service number (10 digits)
    if (!preg_match("/^[0-9]{10}$/", $service)) {
        $error = "Service number must be exactly 10 digits!";
    } else {
        // Fetch bills
        $q = mysqli_query($conn, "SELECT * FROM bills WHERE service_number='$service' ORDER BY bill_date DESC");

        if (mysqli_num_rows($q) == 0) {
            $error = "No bills generated yet for service number $service";
        } else {
            while ($b = mysqli_fetch_assoc($q)) {
                $bills[] = $b;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Bills</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
        small { color: red; }
        button { padding: 5px 10px; cursor: pointer; }
        .pay-button { background-color: #4CAF50; color: white; border: none; }
    </style>
</head>
<body>

<h2>View Bills</h2>

<form method="post" onsubmit="return validateForm()">
    <input type="text" name="service" id="service" placeholder="Enter 10-digit Service Number" oninput="validateService()" required>
    <small id="serviceError"></small><br><br>
    <button name="view">View Bills</button>
</form>

<?php
if ($error != "") {
    echo "<p style='color:red;'>$error</p>";
}

if (!empty($bills)) {
    echo "<h3>Bills for Service Number: $service</h3>";
    echo "<table>
            <tr>
                <th>Bill Number</th>
                <th>Bill Date</th>
                <th>Units</th>
                <th>Total Amount (₹)</th>
                <th>Status</th>
                <th>Due Date (No Fine)</th>
                <th>Due Date (With Fine)</th>
                <th>Payable Amount</th>
                <th>Action</th>
            </tr>";

    foreach ($bills as $b) {

        // Determine if fine applies (based on current date)
        $today = date('Y-m-d');
        $dueNoFine = $b['due_date'];
        $dueWithFine = $b['due_date_fine'];

        $payAmount = $b['total']; // default
        if ($today > $dueNoFine) {
            $payAmount += $b['fine'];
        }

        echo "<tr>
                <td>{$b['bill_number']}</td>
                <td>{$b['bill_date']}</td>
                <td>{$b['units']}</td>
                <td>₹{$b['total']}</td>
                <td>{$b['status']}</td>
                <td>{$dueNoFine}</td>
                <td>{$dueWithFine}</td>
                <td>₹{$payAmount}</td>
                <td>";

        // Show Pay button only for UNPAID bills
        if ($b['status'] === 'UNPAID') {
            echo "<button class='pay-button' onclick=\"payBill('{$b['bill_number']}')\">Pay</button>";
        } else {
            echo "—";
        }

        echo "</td></tr>";
    }

    echo "</table>";
}
?>

<script>
// Validate service number input
function validateService() {
    const service = document.getElementById("service").value;
    const error = document.getElementById("serviceError");

    if (!/^[0-9]*$/.test(service)) {
        error.textContent = "Only digits allowed";
        return false;
    }
    if (service.length > 0 && service.length !== 10) {
        error.textContent = "Service number must be exactly 10 digits";
        return false;
    }
    error.textContent = "";
    return true;
}

function validateForm() {
    return validateService();
}

// Submit payment via POST
function payBill(billNumber) {
    const form = document.createElement('form');
    form.method = 'post';
    form.action = 'pay_bill.php';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'bill_number';
    input.value = billNumber;

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}
</script>

</body>
</html>
