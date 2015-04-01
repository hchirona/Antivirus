<html>
    <head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Antivirus Digital Value</title>
        <link href="css/bootstrap.css" rel="stylesheet">
        <style type="text/css">
            h1 {
                font-family: arial;
                text-align: center;
                padding-top: 10px;
                color: #000000;
            }

            .scanner {
                padding-top: 130px;
                padding-bottom: 20px;
                background: #FFFFFF;
            }

            .scanner p{
                font-family: arial;
                padding-left: 40%;
                margin: 0;
                font-size: 15px;
            }

            .scanner button{
                font-family: arial;
                margin-left: 40%;
                font-size: 15px;
            }

            .g {
                color: #009900;
                font-weight: bold;
            }

            .r {
                color: #990000;
                font-weight: bold;
            }

            .r2 {
                color: #E78034;
            }

            .d {
                color: #AA9797;
            }

            .host {
                margin-left: 5%;
            }

            .summary {
                padding-top: 10px;
                margin-top: 0px;
                background: #E3E2E0;
            }

            .summary p {
                font-size: 12px;
                text-align: center;
                padding: 5px;
                color: #000000;
            }

            .top-labels {
                background: #990033;
            }

            .top-labels p {
                padding: 20px 0px 20px 0px;
                text-align:center;
                color: #000000;
            }

            html{
                background: #E3E2E0;
            }
        </style>
        <title>Panel de control</title>
    </head>
    <body>
        <script>
function popup(){
window.open("http://www.unphp.net/")
}

</script> 

<?php
require('session.php');
require('func.php');
require('config.php');
echo '<div class="top-labels">
    <form action="main.php" method="post" name="session" >
<input name="exit" type="submit" value="Cerrar sessión" style="position:relative; left:90%;">
</form>
<table>
<tr>
<form action="main.php" method=POST>
<input name="main-host" style="width:100px; height:35px"  type="submit" value="HOSTS">
</form>
<form action="main.php" method=POST>
<input name="main-cliente" style="width:100px; height:35px" type="submit" value="CLIENTES">
</form>
<form action="main.php" method=POST>
<input name="main-lector" style="width:100px; height:35px" type="submit" value="LECTOR PHP">
</form>
<form action="main.php" method=POST>
<input name="main-escaner" style="width:100px; height:35px" type="submit" value="ESCANER">
</form>
<form action="main.php" method=POST>
<input name="main-catalogo" style="width:100px; height:35px" type="submit" value="CATALOGO">
</form>
<form action="main.php" method=POST>
<input name="main-log" style="width:100px; height:35px" type="submit" value="LOGS">
</form>
</tr>
</table></div><div class="summary">';
if(isset($_REQUEST["exit"]) && $_REQUEST["exit"]=="Cerrar sessión"){
    login_out();
}elseif(isset($_REQUEST["main-host"]) && $_REQUEST["main-host"]=="HOSTS"){
 echo '<center>
        <h1>Hosts<br></h1>
<FORM ACTION="main.php" METHOD=POST>
       <input name="host-new" type="submit" value="Nuevo host"><br>
</FORM>
</center>';
$lista = mysqli_query($link, "SELECT * FROM host");
$sigue= TRUE;

while ($sigue) {
$host= mysqli_fetch_array($lista);
if ($host) {
echo '<div class="host"><table>
<tr>
<form action="main.php" METHOD=POST>
<input type="hidden" name="host-ip" value="'.$host['ip'].'">
<input type="hidden" name="host-url" value="'.$host['url'].'">
<input type="hidden" name="host-username" value="'.$host['username'].'">
<input type="hidden" name="host-password" value="'.$host['password'].'">
<button name="host-login" type="submit" value="login host">'.$host['nombre'].'</button>
</form>
<form action="main.php" METHOD=POST>
<input type="hidden" name="host-id" value="'.$host['idhost'].'">
<button name="host-info" type="submit" value="info host">Info</button>
</form>
</tr>
</table></div>';
} else {
$sigue = FALSE;
}
}
mysqli_close($link);
}elseif((isset($_REQUEST["host-new"]) && $_REQUEST["host-new"]=="Nuevo host") || (isset($_REQUEST["host-create"])&& $_REQUEST["host-create"]=="Crear")){
if(isset($_REQUEST["host-create"])&& $_REQUEST["host-create"]=="Crear"){

    $nombre=$_REQUEST['nombre'];
    $url=$_REQUEST['url'];
    $ip=$_REQUEST['ip'];
    $username=$_REQUEST['username'];
    $pass1=$_REQUEST['password1'];
    $pass2=$_REQUEST['password2'];
    $cliente=$_REQUEST['cliente-id'];
    echo'<center><h1>Hosts<br></h1>
        '.insert_host($nombre,$url,$ip,$username,$pass1,$pass2,$cliente).'
<CENTER>';
}else{

echo '<center><h1>Hosts<br></h1>
<FORM ACTION="main.php" METHOD=POST>
<CENTER>
<TABLE border="0">
<tr><td>Nombre:</td><td><input type="text" name="nombre" required><br></td></tr>
<tr><td>Url:</td><td><input type="text" name="url" required><br></td></tr>
<tr><td>IP:</td><td><input type="text" name="ip" required><br></td></tr>
<tr><td>Usuario:</td><td><input type="text" name="username" required><br></td></tr>
<tr><td>Contraseña:</td><td><input type="password" name="password1" required><br><td></tr>
<tr><td>Confirmar:</td><td><input type="password" name="password2" required><br><td></tr>
<tr><td><b>Cliente</b></td></tr>';
$lista = mysqli_query($link, "SELECT * FROM cliente");
$sigue= TRUE;

while ($sigue) {
$cliente= mysqli_fetch_array($lista);
if ($cliente) {
echo '<tr><td>
<input type="radio" name="cliente-id" value="'.$cliente['idcliente'].'"> '.$cliente['nombre'].'</td></tr>';
} else {
$sigue = FALSE;
}
}
mysqli_close($link);
echo '<tr><td><INPUT TYPE="submit" name="host-create" VALUE="Crear">
<td><INPUT TYPE="reset" VALUE="Borrar"></td></tr>
</tr>
</TABLE>
</CENTER>
</FORM>';
}

}elseif(((isset($_REQUEST["host-login"]) && $_REQUEST["host-login"]=="login host") || (isset($_REQUEST['ssh-button']) && $_REQUEST['ssh-button']=="Exec") )){
$ssh_url=$_REQUEST['host-url'];
$ssh_server=$_REQUEST['host-ip'];
$ssh_user=$_REQUEST['host-username'];
$ssh_pass=$_REQUEST['host-password'];
$ssh_command=$_REQUEST['ssh-command'];
/*echo '<FORM ACTION="main.php" METHOD=POST>
<input type="hidden" name="host-ip" value="'.$ssh_server.'">
<input type="hidden" name="host-username" value="'.$ssh_user.'">
<input type="hidden" name="host-password" value="'.$ssh_pass.'">
Comando:<input type="text" name="ssh-command">
<INPUT TYPE="submit" name="ssh-button" VALUE="Exec">
</FORM>';
echo $ssh_server ." ". $ssh_command."</br>";*/
        if(!($con = ssh2_connect($ssh_server, 22))){
            echo'No se puede conectar con la máquina '.$ssh_server;
            echo '<form action="main.php" METHOD=POST>
<input type="hidden" name="host-ip" value="'.$ssh_server.'">
<input type="hidden" name="host-url" value="'.$ssh_url.'">
<input type="hidden" name="host-username" value="'.$ssh_user.'">
<input type="hidden" name="host-password" value="'.$ssh_pass.'">
<button name="host-login" type="submit" value="login host">Reintentar</button>
</form>';
        } else {

            if(!ssh2_auth_password($con, $ssh_user, $ssh_pass)) {
                die('Fallo de autentificación en la máquina '.$ssh_server);
            } else {
        echo $ssh_url;
        $md5="cGFzc3dvcmRvdmVycG93ZXI=";
        echo '<iframe name="resultado" src="'.$ssh_url.'?'.$md5.'" width="100%" height="86%" frameborder="0">';
                if(!($stream = ssh2_exec($con, $ssh_command)) ){
                    echo 'Fallo de ejecución de comando en la máquina '.$ssh_server;
                } else {

          stream_set_blocking($stream, true);
          $line = stream_get_line($stream, 1024, "\n");
          while (!feof($stream)){
                  echo $line."</br>";
                  $line = stream_get_line($stream, 1024, "\n");
          }
   fclose($stream);
      }
                }
        }
}elseif((isset($_REQUEST["host-info"]) && $_REQUEST["host-info"]=="info host") || (isset($_REQUEST["host-modify"])&& $_REQUEST["host-modify"]=="Modificar" || (isset($_REQUEST["host-delete"])&& $_REQUEST["host-delete"]=="Eliminar host"))){
if(isset($_REQUEST["host-modify"])&& $_REQUEST["host-modify"]=="Modificar"){
     $id=$_REQUEST['host-id'];
    $nombre=$_REQUEST['nombre'];
    $url=$_REQUEST['url'];
    $ip=$_REQUEST['ip'];
    $username=$_REQUEST['username'];
    $pass1=$_REQUEST['password1'];
    $pass2=$_REQUEST['password2'];
    echo'<center>
        <h1>Hosts<br></h1>
        '.modify_host($id,$nombre,$url,$ip,$username,$pass1,$pass2).'
<CENTER>';

}elseif(isset($_REQUEST["host-delete"])&& $_REQUEST["host-delete"]=="Eliminar host"){
    $id=$_REQUEST['host-id'];
    $nombre=$_REQUEST['host-nombre'];
    echo '<center>
        <h1>Hosts<br></h1>
             '.delete_host($id, $nombre).'

<CENTER>';

}else{
echo '<center><h1>Hosts<br></h1></center>';
$lista = mysqli_query($link, "SELECT * FROM host where idhost='".$_REQUEST['host-id']."'");
$sigue= TRUE;
while ($sigue) {
$host= mysqli_fetch_array($lista);
if ($host) {
echo '<center><table border="0">
<FORM ACTION="main.php" METHOD=POST>
<input type="hidden" name="host-id" value="'.$host['idhost'].'" required>
<tr><td>Nombre:</td><td><input type="text" name="nombre" value="'.$host['nombre'].'" required><br></td></tr>
<tr><td>Url:</td><td><input type="text" name="url" value="'.$host['url'].'" required><br></td></tr>
<tr><td>Ip:</td><td><input type="text" name="ip" value="'.$host['ip'].'" required><br></td></tr>
<tr><td>Usuario:</td><td><input type="text" name="username" value="'.$host['username'].'" required><br></td></tr>
<tr><td>Contraseña:</td><td><input type="password" name="password1" value="'.$host['password'].'" required><br><td></tr>
<tr><td>Confirmar:</td><td><input type="password" name="password2" value="'.$host['password'].'" required><br><td></tr>
<tr><td><INPUT TYPE="submit" name="host-modify" VALUE="Modificar"></td><td><INPUT TYPE="reset" VALUE="Restaurar"></td></tr>
</form></table>
<FORM ACTION="main.php" METHOD=POST>
<input type="hidden" name="host-id" value="'.$host['idhost'].'">
<input type="hidden" name="host-nombre" value="'.$host['nombre'].'"></br>
    ¿Deseas eliminar el host definitivamente?
<INPUT TYPE="submit" name="host-delete" VALUE="Eliminar host"></form></center>';


}else {
$sigue = FALSE;
}
}
mysqli_close($link);
}
}elseif(isset($_REQUEST["main-cliente"]) && $_REQUEST["main-cliente"]=="CLIENTES"){

   echo '<center>
        <h1>Clientes<br></h1>
<FORM ACTION="main.php" METHOD=POST>
       <input name="cliente-new" type="submit" value="Nuevo cliente"><br>
</FORM>
</center>';
$lista = mysqli_query($link, "SELECT * FROM cliente");
$sigue= TRUE;

while ($sigue) {
$cliente= mysqli_fetch_array($lista);
if ($cliente) {
echo '<div class="host"><table>
<tr>
<form action="main.php" METHOD=POST>
<input type="hidden" name="cliente-id" value="'.$cliente['idcliente'].'">
<button name="cliente-info" type="submit" value="info cliente">'.$cliente['nombre'].'</button>
</form>
</tr>
</table></div>';
} else {
$sigue = FALSE;
}
}
mysqli_close($link);
}elseif((isset($_REQUEST["cliente-new"]) && $_REQUEST["cliente-new"]=="Nuevo cliente") || (isset($_REQUEST["cliente-create"])&& $_REQUEST["cliente-create"]=="Crear")){
if(isset($_REQUEST["cliente-create"])&& $_REQUEST["cliente-create"]=="Crear"){

    $nombre=$_REQUEST['nombre'];
    $empresa=$_REQUEST['empresa'];
    $email=$_REQUEST['email'];
    $telefono=$_REQUEST['telefono'];
    echo'<center><h1>Nuevo cliente<br></h1>
        '.insert_client($nombre,$empresa,$email,$telefono).'
<CENTER>';
}else{

echo '<center><h1>Nuevo cliente<br></h1>
    <FORM ACTION="main.php" METHOD=POST>
<CENTER>
<TABLE border="0">
<tr><td>Nombre:</td><td><input type="text" name="nombre" required><br></td></tr>
<tr><td>Empresa:</td><td><input type="text" name="empresa" required><br></td></tr>
<tr><td>Email:</td><td><input type="text" name="email" required><br></td></tr>
<tr><td>Teléfono:</td><td><input type="text" maxlength="9" name="telefono"><br></td></tr>
<tr><td><INPUT TYPE="submit" name="cliente-create" VALUE="Crear">
<td><INPUT TYPE="reset" VALUE="Borrar"></td></tr>
</tr>
</TABLE>
</CENTER>
</FORM>';
}

}elseif((isset($_REQUEST["cliente-info"]) && $_REQUEST["cliente-info"]=="info cliente") || (isset($_REQUEST["cliente-modify"])&& $_REQUEST["cliente-modify"]=="Modificar" || (isset($_REQUEST["cliente-delete"])&& $_REQUEST["cliente-delete"]=="Eliminar cliente"))){
if(isset($_REQUEST["cliente-modify"])&& $_REQUEST["cliente-modify"]=="Modificar"){
     $id=$_REQUEST['cliente-id'];
    $nombre=$_REQUEST['nombre'];
    $empresa=$_REQUEST['empresa'];
    $email=$_REQUEST['email'];
    $telefono=$_REQUEST['telefono'];
    echo'<center>
        <h1>Perfil de Cliente<br></h1>
        '.modify_client($id,$nombre,$empresa,$email,$telefono).'
<CENTER>';
}elseif(isset($_REQUEST["cliente-delete"])&& $_REQUEST["cliente-delete"]=="Eliminar cliente"){
    $id=$_REQUEST['cliente-id'];
    $nombre=$_REQUEST['cliente-nombre'];
    echo '<center>
        <h1>Perdil de Cliente<br></h1>
             '.delete_client($id, $nombre).'

<CENTER>';   
}else{
echo '<center><h1>Perfil de cliente<br></h1></center>';
$lista = mysqli_query($link, "SELECT * FROM cliente where idcliente='".$_REQUEST['cliente-id']."'");
$sigue= TRUE;
while ($sigue) {
$cliente= mysqli_fetch_array($lista);
if ($cliente) {
echo '<center><table border="0">
<FORM ACTION="main.php" METHOD=POST>
<input type="hidden" name="cliente-id" value="'.$cliente['idcliente'].'" required>
<tr><td>Nombre:</td><td><input type="text" name="nombre" value="'.$cliente['nombre'].'" required><br></td></tr>
<tr><td>Empresa:</td><td><input type="text" name="empresa" value="'.$cliente['empresa'].'" required><br></td></tr>
<tr><td>Email:</td><td><input type="text" name="email" value="'.$cliente['email'].'" required><br></td></tr>
<tr><td>Telefono:</td><td><input type="text" maxlength="9" name="telefono" value="'.$cliente['telefono'].'"><br></td></tr>
<tr><td><INPUT TYPE="submit" name="cliente-modify" VALUE="Modificar"></td><td><INPUT TYPE="reset" VALUE="Restaurar"></td></tr>
</form></table>
<FORM ACTION="main.php" METHOD=POST>
<input type="hidden" name="cliente-id" value="'.$cliente['idcliente'].'">
<input type="hidden" name="cliente-nombre" value="'.$cliente['nombre'].'"></br>
    ¿Deseas eliminar el cliente definitivamente?
<INPUT TYPE="submit" name="cliente-delete" VALUE="Eliminar cliente"></form>
        <b>Host</b></br>';

$lista = mysqli_query($link, "SELECT * FROM host where idcliente=".$cliente['idcliente']);
$sigue= TRUE;

while ($sigue) {
$host= mysqli_fetch_array($lista);
if ($host) {
echo '<input type="submit" name="host-cliente" value="'.$host['nombre'].'"></br>';
} else {
$sigue = FALSE;
}
}
echo '</center>';
mysqli_close($link);

}else {
$sigue = FALSE;
}
}
}      
  
}elseif(isset($_REQUEST["main-lector"]) && $_REQUEST["main-lector"]=="LECTOR PHP"){
    echo'<center><h1>Lector de codigo PHP<br></h1></center><div>
  <FORM ACTION="prueba-decode.php" METHOD=POST target="resultado"><CENTER><TABLE border="0">
<TEXTAREA rows="10" cols="180" NAME="var"></TEXTAREA><br>
<b>Tipo de Compresion</b><br>
   <input type="radio" name="compresion" value="">Sin compresion
   <input type="radio" name="compresion" value="gzinflate">gzinflate
   <input type="radio" name="compresion" value="gzuncompress">gzuncompress
<br><br><b>Tipo de Codificacion</b><br>
   <input type="radio" name="codificacion" value="">Sin codificar
   <input type="radio" name="codificacion" value="base64_decode" >base64
<br><br><INPUT TYPE="submit" VALUE="Enviar"><INPUT TYPE="reset" VALUE="Borrar">
</TABLE><center><b>Resultado del codigo PHP introducido:</b><br>
<iframe name="resultado" src="prueba-decode.php" width="90%" height="45%" frameborder="1"></iframe>
</center></CENTER></FORM></body>';
echo '<center><input type=button value="Tambien puedes desofuscar el codigo aqui" onclick="popup()"></center>';

}elseif(isset($_REQUEST["main-escaner"]) && $_REQUEST["main-escaner"]=="ESCANER"){
echo '<iframe name="escaner" src="http://localhost/antivirus/escaner/index.php" width="100%" height="85%" frameborder="0">';
}elseif(isset($_REQUEST["main-catalogo"]) && $_REQUEST["main-catalogo"]=="CATALOGO"){
    echo "prueba de menu main catalogo";

}elseif(isset($_REQUEST["main-log"]) && $_REQUEST["main-log"]=="LOGS"){
    echo "prueba de menu main-log";

}
echo '</body></html>';
?>
