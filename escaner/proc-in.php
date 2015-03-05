<?php
require 'config.php';
$nombre=$_REQUEST['nombre'];
$url=$_REQUEST['url'];
$pass1=$_REQUEST['password1'];
$pass2=$_REQUEST['password2'];
$error="";

if($nombre==""){
$error.=" Nombre ";
}

if($url==""){
$error.= " Url ";
}

if($pass1=="" || $pass2==""){
$error.=" Contraseña vacia ";
}

if($pass1 !== $pass2 ){
$error.="<br>Las contraseñas no coinciden";
}

if($error!==""){
echo "Faltan los siguientes campos: ".$error;
}else{
	$nombre = mysqli_real_escape_string($link,$nombre);
	$url = mysqli_real_escape_string($link,$url);
	$pass1 = mysqli_real_escape_string($link,$pass1);
	$pass1 = sha1(md5($cert.$pass1));
	mysqli_query($link,"INSERT INTO host (idhost,nombre,url,password) VALUES (NULL,'".$nombre."','".$url."','".$pass1."')");
	mysqli_close($link);
header("location: index.php");
}

?>



