<?php
session_start();
if (!isset($_SESSION['supervisor'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Take Meter Reading</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="dashboard-container">

    <h1>Meter Reading Entry</h1>
    <p class="welcome">Supervisor Panel</p>

    <div class="table-box" style="max-width:500px;">

        <form method="POST" action="generate_bill.php">

            <input type="text"
                   name="service_number"
                   placeholder="Service Number"
                   required>

            <input type="number"
                   name="previous_reading"
                   placeholder="Previous Meter Reading"
                   required>

            <input type="number"
                   name="current_reading"
                   placeholder="Current Meter Reading"
                   required>

            <button type="submit">Generate Bill</button>

        </form>

    </div>

</div>

</body>
</html>