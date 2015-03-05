<?php
include('session.php');
?>
<!DOCTYPE html>
<html>
<head>
<title>Your Home Page</title>
</head>
<body>
<div id="profile">
<b id="welcome">Bienvenido: <i><?php echo $_SESSION['hostname']; ?></i></b>
<b id="logout"><a href="logout.php">Log Out</a></b>
<FORM ACTION="<?php echo $_SESSION['hosturl']; ?>" METHOD=POST target="prueba1">
<input type="hidden" name="valido" value="valido">
<INPUT TYPE="submit" VALUE="Enviar">
<iframe name="prueba1" src="<?php echo $_SESSION['hosturl']; ?>" width="90%" height="50%" ></iframe>
</form>
</div>
</body>
</html>
