<?php
require('config.php');
session_start();
$user_check=$_SESSION['iduser'];
$ses_sql=mysqli_query($link,"select * from usuario where iduser='$user_check'");
$row = mysqli_fetch_assoc($ses_sql);
$login_session =$row['iduser'];
$userData= mysqli_fetch_array($ses_sql);
if(!isset($login_session)){
mysqli_close($link);
header('Location: index.php');
}
?>