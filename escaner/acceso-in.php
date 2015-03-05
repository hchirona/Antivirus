<?php
include('login-in.php');
if(isset($_SESSION['login_user'])){
header("location: profile.php");
}

$idhost=$_REQUEST['idhost'];
$nombre=$_REQUEST['nombre'];
$url=$_REQUEST['url'];

?>
<!DOCTYPE html>
<html>
<head>
<title>Login</title>
</head>
<body>
<div id="main">
<div id="login">
<h2></h2>
<form action="" method="post">
<label>Dominio: <?php echo $nombre ?></label><br>
<input type="hidden" name="idhost" value="<?php echo $idhost;  ?>">
<input type="hidden" name="nombre" value="<?php echo $nombre; ?>">
<input type="hidden" name="url" value="<?php echo $url; ?>">
<label>Password :</label>
<input id="password" name="password" placeholder="**********" type="password">
<input name="submit" type="submit" value=" Login ">
<span><?php echo $error;?></span>
</form>
</div>
</div>
</body>
</html>
