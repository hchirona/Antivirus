<?php
/*
//   -------------------------------------------------------------------------------
//  |                  Antivirus DV: Malware scan software                          |
//  |              Copyright (c) 2015 by Héctor Chirona Torrentí                    |
//  |                                                                               |
//   -------------------------------------------------------------------------------
*/


function insert_host($nombre,$url,$ip,$username,$pass1,$pass2,$cliente){
    require('config.php');
    $error="";
    if($nombre==""){
        $error.=" Nombre ";
    }
    if($url==""){
        $error.=" Url ";
    }
    if($ip==""){
        $error.= " IP ";
    }
    if($username==""){
        $error.= " Usuario ";
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
        $url=mysqli_real_escape_string($link,$url);;
        $ip = mysqli_real_escape_string($link,$ip);
        $pass1 = mysqli_real_escape_string($link,$pass1);
        $cliente=mysqli_real_escape_string($link,$cliente);
        mysqli_query($link,"INSERT INTO host (idhost,nombre,url,ip,username,password,idcliente) VALUES (NULL,'".$nombre."','".$url."','".$ip."','".$username."','".$pass1."','".$cliente."')");
        mysqli_close($link);
    }
    return $error;
}

function modify_host($id,$nombre,$url,$ip,$username,$pass1,$pass2){
    require('config.php');
    $error="";
    if($nombre==""){
        $error.=" Nombre ";
    }
    if($url==""){
        $error.=" Url ";
    }
    if($ip==""){
        $error.= " IP ";
    }
    if($username==""){
        $error.= " Usuario ";
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
       $error="El host ha sido modificado correctamente";
       $nombre = mysqli_real_escape_string($link,$nombre);
       $url=mysqli_real_escape_string($link,$url);;
       $ip = mysqli_real_escape_string($link,$ip);
       $pass1 = mysqli_real_escape_string($link,$pass1);
       $cliente=mysqli_real_escape_string($link,$cliente);
       mysqli_query($link,"UPDATE host SET nombre='".$nombre."',url='".$url."',ip='".$ip."',username='".$username."',password='".$pass1."' WHERE idhost =".$id.";");
       mysqli_close($link);
   }
   return $error;
}

function delete_host($id,$nombre){
    require('config.php');
    mysqli_query($link,"DELETE FROM host WHERE idhost ='".$id."' and nombre='".$nombre."'");
    mysqli_close($link);
    $error="El host se ha eliminado correctamente";
    return $error;
}

function insert_client($nombre,$empresa,$email,$telefono){
    require('config.php');
    $error="";
    if($nombre==""){
        $error.=" Nombre ";
    }
    if($empresa==""){
        $error.=" Empresa ";
    }
    if($email==""){
        $error.= " Email ";
    }
    if($telefono==""){
        $error.= " Telefono ";
    }
    if($error!==""){
        echo "Faltan los siguientes campos: ".$error;
    }else{
       $error="El cliente ha sido creado correctamente";
       $nombre = mysqli_real_escape_string($link,$nombre);
       $empresa=mysqli_real_escape_string($link,$empresa);;
       $email = mysqli_real_escape_string($link,$email);
       $telefono = mysqli_real_escape_string($link,$telefono);
       mysqli_query($link,"INSERT INTO cliente (nombre,empresa,email,telefono) VALUES ('".$nombre."','".$empresa."','".$email."','".$telefono."')");
       mysqli_close($link);
   }
   return $error;    
}

function delete_client($id,$nombre){
    require('config.php');
    mysqli_query($link,"DELETE FROM cliente WHERE idcliente ='".$id."' and nombre='".$nombre."'");
    mysqli_close($link);
    $error="El cliente se ha eliminado correctamente";
    return $error;
}

function modify_client($id,$nombre,$empresa,$email,$telefono){
    require('config.php');
    $error="";
    if($nombre==""){
        $error.=" Nombre ";
    }
    if($empresa==""){
        $error.=" Empresa ";
    }
    if($email==""){
        $error.= " Email ";
    }
    if($telefono==""){
        $error.= " Telefono ";
    }
    if($error!==""){
        echo "Faltan los siguientes campos: ".$error;
    }else{
       $error="El cliente ha sido modificado correctamente";
       $nombre = mysqli_real_escape_string($link,$nombre);
       $empresa=mysqli_real_escape_string($link,$empresa);;
       $email= mysqli_real_escape_string($link,$email);
       $telefono= mysqli_real_escape_string($link,$telefono);
       mysqli_query($link,"UPDATE cliente SET nombre='".$nombre."',empresa='".$empresa."',email='".$email."',telefono='".$telefono."' WHERE idcliente =".$id.";");
       mysqli_close($link);
   }
   return $error;
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

function preg_decode($texto){
    $l1= preg_replace('/preg_replace\(\"\/\.\*\/e\"\,\"/', '', $texto);
        $l2= preg_replace('/\'.*/', '', $l1);
        $l3= 'print "'.$l2.'";';
        $l4= preg_replace('/^.*\'/' ,'', $l1);
        $l5= preg_replace('/\".*/' ,'' ,$l4);
        $l6= 'print "'.$l5.'";';
        $l7= preg_replace('/^.*?\'/','"',$l1);
        $l8= preg_replace('/\'.*/','"',$l7);

        ob_start();
        echo eval($l3);
        echo "$l8";
        echo eval($l6);
        $resultado=ob_get_clean();
        return $resultado;
    }



/*
function insert_user($nombre,$username,$pass1,$pass2){
    require('config.php');
	$error="El host ha sido creado correctamente";
        $nombre = mysqli_real_escape_string($link,$nombre);
        $username = mysqli_real_escape_string($link,$username);
        $pass1 = mysqli_real_escape_string($link,$pass1);
        $pass1 = sha1(md5($cert.$pass1));
        mysqli_query($link,"INSERT INTO usuario (iduser,nombre,username,password) VALUES (NULL,'".$nombre."','".$username."','".$pass1."')");
        mysqli_close($link);
	header("location: index.php");
return $error;
}*/


?>
