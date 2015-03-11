<html>
<head>
    <title>Antivirus</title>
</head>
<body>
    <center>
        <h1>Hosts<br></h1>
<FORM ACTION="new.php" METHOD=POST target="resultado">
       <INPUT TYPE="submit" VALUE="Nuevo host"><br>
</FORM>
</center>
<?php
require 'config.php';
$lista = mysqli_query($link, "SELECT * FROM host");
$numfilas = mysqli_num_rows($lista);
$n=0;
$sigue= TRUE;
mysql_data_seek($lista,0);

while ($sigue) {
$host= mysqli_fetch_array($lista);
if ($host) {
?>
<table>
<tr>
<form action="panel.php" METHOD=POST>
<input type="hidden" name="idhost" value="<?php echo $host['idhost'];  ?>">
<input type="hidden" name="nombre" value="<?php echo $host['nombre']; ?>">
<input type="hidden" name="url" value="<?php echo $host['url']; ?>">
<button type="submit"><?php echo $host['nombre']; ?></button>
</form>
<form action="datos-host.php" METHOD=POST>
<input type="hidden" name="idhost" value="<?php echo $host['idhost'];  ?>">
<input type="hidden" name="nombre" value="<?php echo $host['nombre']; ?>">
<input type="hidden" name="url" value="<?php echo $host['url']; ?>">
<button type="submit">Info</button>
</form>
</tr>
</table>
<?php
} else {
$sigue = FALSE;
}
}

mysqli_close($link);


?>

</body>
</html>

