<?php
require('session.php');

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
<input name="main-host" type="submit" value="HOSTS">
</form>
<form action="main.php" method=POST>
<input name="main-cliente" type="submit" value="CLIENTES">
</form>
<form action="main.php" method=POST>
<input name="main-lector" type="submit" value="LECTOR PHP">
</form>
<form action="main.php" method=POST>
<input name="main-escaner" type="submit" value="ESCANER">
</form>
<form action="main.php" method=POST>
<input name="main-catalogo" type="submit" value="CATALOGO">
</form>
<form action="main.php" method=POST>
<input name="main-log" type="submit" value="LOGS">
</form>
</tr>
</table>';
if(isset($_REQUEST["exit"]) && $_REQUEST["exit"]=="Cerrar sessión"){
    require('func.php');
    login_out();
}elseif(isset($_REQUEST["main-host"]) && $_REQUEST["main-host"]=="HOSTS"){
 echo '<center>
        <h1>Hosts<br></h1>
<FORM ACTION="main.php" METHOD=POST>
       <input name="host-new" type="submit" value="Nuevo host"><br>
</FORM>
</center>';
require('config.php');
$lista = mysqli_query($link, "SELECT * FROM host");
$numfilas = mysqli_num_rows($lista);
$n=0;
$sigue= TRUE;
mysql_data_seek($lista,0);

while ($sigue) {
$host= mysqli_fetch_array($lista);
if ($host) {
echo '<table>
<tr>
<form action="main.php" METHOD=POST>
<input type="hidden" name="host-idhost" value="'.$host['idhost'].'">
<input type="hidden" name="host-nombre" value="'.$host['nombre'].'">
<input type="hidden" name="host-url" value="'.$host['url'].'">
<button name="host-login" type="submit" value="login host">'.$host['nombre'].'</button>
</form>
<form action="main.php" METHOD=POST>
<input type="hidden" name="host-idhost" value="'.$host['idhost'].'">
<input type="hidden" name="host-nombre" value="'.$host['nombre'].'">
<input type="hidden" name="host-url" value="'.$host['url'].'">
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
    require('func.php');
    $var=$_REQUEST["button"];
    $nombre=$_REQUEST['nombre'];
    $ip=$_REQUEST['ip'];
    $pass1=$_REQUEST['password1'];
    $pass2=$_REQUEST['password2'];
    echo'<html>
    <center>
        <h1>Hosts<br></h1>
        '.insert_host($nombre,$ip,$pass1,$pass2).'
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
<tr><td>IP:</td><td><input type="text" name="ip"><br></td></tr>
<tr><td>Password:</td><td><input type="password" name="password1"><br><td></tr>
<tr><td>Confirmar:</td><td><input type="password" name="password2"><br><td></tr>
<tr><td><INPUT TYPE="submit" name="host-create" VALUE="Crear">
<td><INPUT TYPE="reset" VALUE="Borrar"></td></tr>
</tr>
</TABLE>
</CENTER>
</FORM>
</html>';

}
}elseif((isset($_REQUEST["host-info"]) && $_REQUEST["host-info"]=="info host")){

    
}elseif(isset($_REQUEST["main-cliente"]) && $_REQUEST["main-cliente"]=="CLIENTES"){
    
}
elseif(isset($_REQUEST["main-lector"]) && $_REQUEST["main-lector"]=="LECTOR PHP"){
    echo'<body>
    <center>
        <h1>Lector de codigo PHP<br></h1>
    </center>
    <div>
  <FORM ACTION="decode.php" METHOD=POST target="resultado">

<CENTER>
<TABLE border="0">

   <TEXTAREA rows="10" cols="100" NAME="var" "></TEXTAREA><br>

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

<iframe name="resultado" src="decode.php" width="90%" height="50%" ></iframe>
</center>


</CENTER>
</FORM>
        </div>


</body>';
}
elseif(isset($_REQUEST["main-escaner"]) && $_REQUEST["main-escaner"]=="ESCANER"){
    
}
elseif(isset($_REQUEST["main-catalogo"]) && $_REQUEST["main-catalogo"]=="CATALOGO"){
    
}
elseif(isset($_REQUEST["main-log"]) && $_REQUEST["main-log"]=="LOGS"){
    

}else{



}
echo '</body></html>';
?>