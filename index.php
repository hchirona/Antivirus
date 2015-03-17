<?php
if(isset($_SESSION['login_user'])){
header("location: main.php");
}
if(isset($_REQUEST["login"]) || $_REQUEST["login"]=="Login"){
    require('func.php');
    $user=$_REQUEST['username'];
    $pass=$_REQUEST['password'];
    echo login_in($user,$pass);
}else{
echo '<html>
<head>
<title>Panel de acceso</title>
</head>
<body>
<form action="index.php" method="post" name="session">
<label>Usuario :</label>
<input id="name" name="username" placeholder="username" type="text">
<label>Contraseña :</label>
<input id="password" name="password" placeholder="******" type="password">
<input name="login" type="submit" value="Login">
<span>'.$error.'</span>
</form>
</body>
</html>';
}
?>