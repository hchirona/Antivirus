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
}
return $error;
}

function delete_host(){
require('config.php');
mysqli_query($link,"DELETE FROM host WHERE idhost ='".$idhost."' and nombre='".$nombre."'");
        mysqli_close($link);
$error="El host se ha eliminado correctamente";
header("location: main.php");
return $error;
}

function insert_client(){
}

function delete_client(){
}


function login_out(){
    session_start();
    if(session_destroy())
    {
    header("Location: index.php");
    }
}

function login_in($username,$password){
    session_start();
    require('config.php');
    $error='';
    if ($username=="" || $password=="") {
        $error = "Usuario o contraseña vacio";
    }else{
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
        }else{
            $error = "Usuario o contraseña invalidos";
        }
    mysqli_close($link);
        }
    
    return $error;
}
/*
function insert_user($nombre,$username,$pass1,$pass2){
    require('config.php');
	$error="El host ha sido creado correctamente";
        $nombre = mysqli_real_escape_string($link,$nombre);
        $username = mysqli_real_escape_string($link,$ip);
        $pass1 = mysqli_real_escape_string($link,$pass1);
        $pass1 = sha1(md5($cert.$pass1));
        mysqli_query($link,"INSERT INTO usuario (iduser,nombre,username,password) VALUES (NULL,'".$nombre."','".$username."','".$pass1."')");
        mysqli_close($link);
	header("location: index.php");
return $error;
}*/
?>
