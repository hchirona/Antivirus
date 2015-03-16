<?php
require('session.php');
require('func.php');
require('config.php');
echo '<html>
<title>Menú</title>
<body>
<b id="welcome">Bienvenido: <i>'.$_SESSION['nombre'].'</i></b>
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
<input type="hidden" name="host-idhost" value="'.$host['idhost'].'">
<input type="hidden" name="host-nombre" value="'.$host['nombre'].'">
<input type="hidden" name="host-ip" value="'.$host['ip'].'">
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

    $var=$_REQUEST["button"];
    $nombre=$_REQUEST['nombre'];
    $url=$_REQUEST['url'];
    $ip=$_REQUEST['ip'];
    $username=$_REQUEST['username'];
    $pass1=$_REQUEST['password1'];
    $pass2=$_REQUEST['password2'];
    echo'<html>
    <center>
        <h1>Hosts<br></h1>
        '.insert_host($nombre,$url,$ip,$username,$pass1,$pass2).'
<CENTER>
</html>';
}else{

echo '<html>
    <center>
        <h1>Hosts<br></h1>
    <FORM ACTION="main.php" METHOD=POST>
<CENTER>
<TABLE border="0">
<tr><td>Nombre:</td><td><input type="text" name="nombre"><br></td></tr>
<tr><td>Url:</td><td><input type="text" name="url"><br></td></tr>
<tr><td>IP:</td><td><input type="text" name="ip"><br></td></tr>
<tr><td>Usuario:</td><td><input type="text" name="username"><br></td></tr>
<tr><td>Contraseña:</td><td><input type="password" name="password1"><br><td></tr>
<tr><td>Confirmar:</td><td><input type="password" name="password2"><br><td></tr>
<tr><td><INPUT TYPE="submit" name="host-create" VALUE="Crear">
<td><INPUT TYPE="reset" VALUE="Borrar"></td></tr>
</tr>
</TABLE>
</CENTER>
</FORM>
</html>';

}
}elseif(((isset($_REQUEST["host-login"]) && $_REQUEST["host-login"]=="login host") || (isset($_REQUEST['ssh-button']) && $_REQUEST['ssh-button']=="Exec") )){
echo "prueba de menu host-login<br>";
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
        } else {

            if(!ssh2_auth_password($con, $ssh_user, $ssh_pass)) {
                die('Fallo de autentificación en la máquina '.$ssh_server);
            } else {
        echo $ssh_url;
        $connection="cGFzc3dvcmRvdmVycG93ZXI=7cf1c07c7c419ce659ef378559cccaa9";
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
echo $ssh_url;
        $connection="cGFzc3dvcmRvdmVycG93ZXI=7cf1c07c7c419ce659ef378559cccaa9";
echo '<iframe name="resultado" src="'.$ssh_url.'?'.$connection.'" width="100%" height="85%" frameborder="1">';
  
}elseif((isset($_REQUEST["host-info"]) && $_REQUEST["host-info"]=="info host")){
echo "prueba de menu host-info";

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

}
elseif(isset($_REQUEST["main-escaner"]) && $_REQUEST["main-escaner"]=="ESCANER"){
    echo "prueba de menu main escaner";
echo '<iframe name="escaner" src="http://localhost/escaner/index.php" width="100%" height="85%" frameborder="1">';
}elseif(isset($_REQUEST["main-catalogo"]) && $_REQUEST["main-catalogo"]=="CATALOGO"){
    echo "prueba de menu main catalogo";

}elseif(isset($_REQUEST["main-log"]) && $_REQUEST["main-log"]=="LOGS"){
    echo "prueb de menu main-log";

}else{
echo "prueba de home";

}
echo '</body></html>';
?>
