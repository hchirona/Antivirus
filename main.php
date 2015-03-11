<?php
include('session.php');
?>
<!DOCTYPE html>
<html>
<head>
<title>Menú</title>
</head>
<body>
<div id="profile">
<b id="welcome">Bienvenido: <i><?php echo $_SESSION['nombre']; ?></i></b>
<b id="logout"><a href="logout.php">Cerrar Session</a></b><br>
</div>
</body>
</html>

