<?php
$sql_server="localhost";
$sql_user="root";
$sql_pass="admin";
$sql_db="db_virus";
$cert="dv.e";

$link=mysqli_connect($sql_server, $sql_user, $sql_pass);
if (!$link){
	die('Could not connect: ' . mysqli_error($link));
}

mysqli_select_db($link,$sql_db);


?>
