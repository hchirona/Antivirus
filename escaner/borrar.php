<?php
include('session.php');
echo "Seguro que desea elimar el host ".$_SESSION['hostname']."?<br>";
?>
<table>
<tr>
 <FORM ACTION="proc-rm.php" METHOD=POST>
	<input type="hidden" name="idhost" value="<?php echo $_SESSION['hostid'];  ?>">
	<input type="hidden" name="nombre" value="<?php echo $_SESSION['hostname']; ?>">
	<button type="submit">Confirmar</button>
       	</FORM>
<a href='index.php'><button>Cancelar</button></a>
</tr>
</table>
