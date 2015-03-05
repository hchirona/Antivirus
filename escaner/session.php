<?php
require 'config.php';
session_start();
$check=$_SESSION['hostid'];
$ses_sql=mysqli_query($link,"select * from host where idhost='$check'");
$row = mysqli_fetch_assoc($ses_sql);
$login_session =$row['idhost'];
$hostdata= mysqli_fetch_array($ses_sql);
global $hostdata;
if(!isset($login_session)){
mysqli_close($link);
header('Location: index.php');
}
?>
