<?php
require('func.php');
$nombre=$_REQUEST['nombre'];
$ip=$_REQUEST['ip'];
$pass1=$_REQUEST['password1'];
$pass2=$_REQUEST['password2'];

echo insert_host($nombre,$ip,$pass1,$pass2);


?>



