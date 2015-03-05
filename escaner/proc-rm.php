<?php
require 'config.php';
include('session.php');
$idhost=$_SESSION['hostid'];
$nombre=$_SESSION['hostname'];
mysqli_query($link,"DELETE FROM host WHERE idhost ='".$idhost."' and nombre='".$nombre."'");
        mysqli_close($link);
header("location: index.php");
?>
