<?php
session_start();
include "db.php";

$role = $_POST['role'];
$user = $_POST['username'];
$pass = $_POST['password'] ?? '';
$phone = $_POST['phone'] ?? '';

if($role=="admin"){
    $p = md5($pass);
    $q = mysqli_query($conn,"SELECT * FROM admin WHERE username='$user' AND password='$p'");
    if(mysqli_num_rows($q)) { 
        $_SESSION['admin'] = $user; 
        header("Location: admin_dashboard.php"); 
    } else echo "Invalid Admin Login";
}

elseif($role=="supervisor"){
    $p = md5($pass);
    $q = mysqli_query($conn,"SELECT * FROM supervisors WHERE username='$user' AND password='$p'");
    if(mysqli_num_rows($q)) { 
        $_SESSION['supervisor'] = $user; 
        header("Location: supervisor_dashboard.php"); 
    } else echo "Invalid Supervisor Login";
}

elseif($role=="user"){
    // Fetch user row
    $q = mysqli_query($conn,"SELECT * FROM users WHERE service_number='$user'");
    if(mysqli_num_rows($q)) { 
        $row = mysqli_fetch_assoc($q);
        $_SESSION['user'] = (int)$row['service_number']; // Force numeric
        header("Location: user_dashboard.php");
    } else echo "Invalid User Login";
}
?>
