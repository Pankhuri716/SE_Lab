<?php
session_start();
if(!isset($_SESSION['admin'])) header("Location: login.html");
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
<link rel="stylesheet" href="style.css">
<style>
/* Success message styling */
.alert-success {
    max-width: 600px;
    margin: 15px auto;
    padding: 12px 15px;
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    border-radius: 6px;
    text-align: center;
    font-weight: bold;
}
</style>
</head>
<body>

<div class="dashboard-container">

<h1>Admin Dashboard</h1>
<p class="welcome">Welcome, Admin</p>

<!-- ===== SUCCESS MESSAGE ===== -->
<?php
if(isset($_SESSION['msg'])){
    echo '<div class="alert-success">'.$_SESSION['msg'].'</div>';
    unset($_SESSION['msg']); // Clear the message after displaying
}
?>

<div class="card-container">

    <a href="add_user.php" class="card">
        <h3>Add User</h3>
        <p>Register electricity consumer</p>
    </a>

    <a href="add_supervisor.php" class="card">
        <h3>Add Supervisor</h3>
        <p>Create supervisor account</p>
    </a>

    <a href="view_all_bills.php" class="card">
        <h3>View All Bills</h3>
        <p>View electricity bills of all users</p>
    </a>

    <a href="logout.php" class="card logout">
        <h3>Logout</h3>
        <p>Exit admin panel</p>
    </a>

</div>

</div>

</body>
</html>