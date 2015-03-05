<html>
<head>
    <title>Antivirus</title>
</head>

<body>
<!--
 <form action="index.php" method="post">
 <input type="submit" value="Pagina principal">
 </form>
 <form action="new.php" method="post">
 <input type="submit" value="Nuevo virus">
 </form>
-->
    <center>
        <h1>Lector de codigo PHP<br></h1>
    </center>
    <div>
  <FORM ACTION="resultado.php" METHOD=POST target="resultado">

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

<iframe name="resultado" src="resultado.php" width="90%" height="50%" ></iframe>
</center>


</CENTER>
</FORM>
        </div>


</body>
</html>
