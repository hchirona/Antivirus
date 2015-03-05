<?php
session_start();
require 'config.php';
$error='';
if (isset($_REQUEST['submit'])) {
if (empty($_REQUEST['idhost']) || empty($_REQUEST['password'])) {
$error = "Contraseña incorrecta";
}
else
{
$idhost=$_REQUEST['idhost'];
global $idhost;
$password=$_REQUEST['password'];
$password = mysqli_real_escape_string($link,$password);
$password = sha1(md5($cert.$password));
$query = mysqli_query($link,"select * from host where password='$password' AND idhost='$idhost'");
$rows = mysqli_num_rows($query);
if ($rows == 1) {
$host= mysqli_fetch_array($query);
$_SESSION['hostid']=$host['idhost'];
$_SESSION['hostname']=$host['nombre'];
$_SESSION['hosturl']=$host['url'];
header("location: profile.php");
} else {
$error = "Usuario o contraseña invalidos";
}
mysqli_close($link);
}
}
?>
