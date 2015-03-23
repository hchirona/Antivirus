<?php
require('session.php');
require('func.php');
require('config.php');
echo '<b id="welcome">Bienvenido: <i>'.$_SESSION['nombre'].'</i></b>
    <form action="main.php" method="post" name="session">
<input name="exit" type="submit" value="Cerrar sessión">
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
</table>';
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
echo '<table>
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
</table>';
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
    echo'<center><h1>Hosts<br></h1>
        '.insert_host($nombre,$url,$ip,$username,$pass1,$pass2).'
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
<tr><td><INPUT TYPE="submit" name="host-create" VALUE="Crear">
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
            /*echo '<form action="main.php" METHOD=POST>
<input type="hidden" name="host-ip" value="'.$ssh_server.'">
<input type="hidden" name="host-url" value="'.$ssh_url.'">
<input type="hidden" name="host-username" value="'.$ssh_user.'">
<input type="hidden" name="host-password" value="'.$ssh_pass.'">
<button name="host-login" type="submit" value="login host">Reintentar</button>
</form>';*/
        } else {

            if(!ssh2_auth_password($con, $ssh_user, $ssh_pass)) {
                die('Fallo de autentificación en la máquina '.$ssh_server);
            } else {
        echo $ssh_url;
        $connection="cGFzc3dvcmRvdmVycG93ZXI=f3a83e2633bfa74ec08318f4f9cc99fb";
        echo '<iframe name="resultado" src="'.$ssh_url.'?'.$connection.'" width="100%" height="85%" frameborder="1">';
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
    //////////////////////////////////////////////////////////////////////////////////
}elseif(isset($_REQUEST["host-delete"])&& $_REQUEST["host-delete"]=="Eliminar host"){
    $id=$_REQUEST['host-id'];
    $nombre=$_REQUEST['host-nombre'];
    echo '<center>
        <h1>Hosts<br></h1>
             '.delete_host($id, $nombre).'  

<CENTER>';
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
}else{
echo '<center><h1>Hosts<br></h1></center>';
$lista = mysqli_query($link, "SELECT * FROM host where idhost='".$_REQUEST['host-id']."'");
$sigue= TRUE;
while ($sigue) {
$host= mysqli_fetch_array($lista);
if ($host) {
echo '<TABLE border="0">
<FORM ACTION="main.php" METHOD=POST>
<input type="hidden" name="host-id" value="'.$host['idhost'].'" required>
<tr><td>Nombre:</td><td><input type="text" name="nombre" value="'.$host['nombre'].'" required><br></td></tr>
<tr><td>Url:</td><td><input type="text" name="url" value="'.$host['url'].'" required><br></td></tr>
<tr><td>Ip:</td><td><input type="text" name="ip" value="'.$host['ip'].'" required><br></td></tr>
<tr><td>Usuario:</td><td><input type="text" name="username" value="'.$host['username'].'" required><br></td></tr>
<tr><td>Cambiar contraseña:</td><td><input type="password" name="password1" value="'.$host['password'].'" required><br><td></tr>
<tr><td>Confirmar:</td><td><input type="password" name="password2" value="'.$host['password'].'" required><br><td></tr>
<tr><td><INPUT TYPE="submit" name="host-modify" VALUE="Modificar">
<td><INPUT TYPE="reset" VALUE="Restaurar"></td></tr>
</tr>
</TABLE></form>';
echo '<FORM ACTION="main.php" METHOD=POST>
<input type="hidden" name="host-id" value="'.$host['idhost'].'">
<input type="hidden" name="host-nombre" value="'.$host['nombre'].'">
<INPUT TYPE="submit" name="host-delete" VALUE="Eliminar host"></form>';


}else {
$sigue = FALSE;
}
}
}
}elseif(isset($_REQUEST["main-cliente"]) && $_REQUEST["main-cliente"]=="CLIENTES"){
 echo "prueba de menu main-cliente";

}elseif(isset($_REQUEST["main-lector"]) && $_REQUEST["main-lector"]=="LECTOR PHP"){
   /* echo'<body>
    <center>
        <h1>Lector de codigo PHP<br></h1>
    </center>
    <div>
  <FORM ACTION="decode.php" METHOD=POST target="resultado">

<CENTER>
<TABLE border="0">

   <TEXTAREA rows="12" cols="200" NAME="var"></TEXTAREA><br>

   <b>Tipo de Compresion</b>
<br>
   <input type="radio" name="compresion" value="">Sin compresion
   <input type="radio" name="compresion" value="gzinflate">gzinflate
   <input type="radio" name="compresion" value="gzuncompress">gzuncompress
<br><br>
   <b>Tipo de Codificacion</b>
<br>
   <input type="radio" name="codificacion" value="">Sin codificar
   <input type="radio" name="codificacion" value="base64_decode" >base64
<br><br>
       <INPUT TYPE="submit" VALUE="Enviar">
       <INPUT TYPE="reset" VALUE="Borrar">

</TABLE>
<center>
<b>Resultado del codigo PHP introducido:</b><br>

<iframe name="resultado" src="decode.php" width="90%" height="45%" frameborder="1"></iframe>
</center>


</CENTER>
</FORM>
        </div>

</body>';*/
    echo '<iframe name="unphp" src="http://www.unphp.net/" width="100%" height="90%" frameborder="0" ></iframe>';

}elseif(isset($_REQUEST["main-escaner"]) && $_REQUEST["main-escaner"]=="ESCANER"){
    echo "prueba de menu main escaner";
echo '<iframe name="escaner" src="http://localhost/antivirus/escaner/index.php" width="100%" height="85%" frameborder="1">';
}elseif(isset($_REQUEST["main-catalogo"]) && $_REQUEST["main-catalogo"]=="CATALOGO"){
    echo "prueba de menu main catalogo";

}elseif(isset($_REQUEST["main-log"]) && $_REQUEST["main-log"]=="LOGS"){
    echo "prueb de menu main-log";

}else{
echo "prueba de home";

}
echo '</body></html>';
?>
