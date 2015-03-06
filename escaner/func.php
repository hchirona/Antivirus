<?php

function insert_host($nombre,$ip,$pass1,$pass2){
require('config.php');
$error="";

if($nombre==""){
$error.=" Nombre ";
}

if($ip==""){
$error.= " IP ";
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
	$error="El host ha sido creado correctamente";
        $nombre = mysqli_real_escape_string($link,$nombre);
        $ip = mysqli_real_escape_string($link,$ip);
        $pass1 = mysqli_real_escape_string($link,$pass1);
        $pass1 = sha1(md5($cert.$pass1));
        mysqli_query($link,"INSERT INTO host (idhost,nombre,ip,password) VALUES (NULL,'".$nombre."','".$ip."','".$pass1."')");
        mysqli_close($link);
	header("location: index.php");
}
return $error;
}

function delete_host(){
}

function insert_client(){
}

function delete_client(){
}

function cript($pass){
require('config.php');
        $pass = mysqli_real_escape_string($link,$pass);
        $pass = sha1(md5($cert.$pass1));
	return $pass;
}
?>
