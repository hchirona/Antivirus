 <?php

if(isset($_REQUEST["button"])|| $_REQUEST["button"]=="Crear"){
    require('func.php');
    $var=$_REQUEST["button"];
    $nombre=$_REQUEST['nombre'];
    $ip=$_REQUEST['ip'];
    $pass1=$_REQUEST['password1'];
    $pass2=$_REQUEST['password2'];
   echo insert_host($nombre,$ip,$pass1,$pass2) ;
}else{

echo '<html>
    <FORM ACTION="new.php" METHOD=POST>
<CENTER>
<TABLE border="0">
<tr><td>Nombre:</td><td><input type="text" name="nombre"><br></td></tr>
<tr><td>IP:</td><td><input type="text" name="ip"><br></td></tr>
<tr><td>Password:</td><td><input type="password" name="password1"><br><td></tr>
<tr><td>Confirmar:</td><td><input type="password" name="password2"><br><td></tr>
<tr><td><INPUT TYPE="submit" name="button" VALUE="Crear">
<td><INPUT TYPE="reset" VALUE="Borrar"></td></tr>
</tr>
</TABLE>
</CENTER>
</FORM>
</html>';

}
?>
