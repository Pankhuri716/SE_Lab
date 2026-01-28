<?php
session_start();
include "db.php";

if(isset($_POST['login'])){
$q=mysqli_query($conn,"SELECT * FROM supervisors
WHERE username='$_POST[username]' AND password=MD5('$_POST[password]')");
if(mysqli_num_rows($q)==1){
$_SESSION['sup']=$_POST['username'];
header("Location: supervisor_dashboard.php");
}
}
?>

<form method="post">
<h2>Supervisor Login</h2>
<input name="username" required>
<input type="password" name="password" required>
<button name="login">Login</button>
</form>
