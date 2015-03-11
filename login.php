<?php
session_start();
require('config.php');
$error='';
if (isset($_REQUEST['submit'])) {
if (empty($_REQUEST['username']) || empty($_REQUEST['password'])) {
$error = "Usuario o contraseña vacio";
}
else
{
$username=$_REQUEST['username'];
$password=$_REQUEST['password'];
$username = mysqli_real_escape_string($link,$username);
$password = mysqli_real_escape_string($link,$password);
$password=sha1(md5($cert.$password));
$query = mysqli_query($link,"select * from usuario where password='$password' AND username='$username'");
$rows = mysqli_num_rows($query);
if ($rows == 1) {
$user= mysqli_fetch_array($query);
$_SESSION['iduser']=$user['iduser'];
$_SESSION['nombre']=$user['nombre'];
$_SESSION['username']=$user['username'];
header("location: main.php");
} else {
$error = "Usuario o contraseña invalidos";
}
mysqli_close($link);
}
}
?>
