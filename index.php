<?php
include('login.php');
if(isset($_SESSION['login_user'])){
header("location: main.php");
}

echo '<html>
<head>
<title>Panel de acceso</title>
</head>
<body>
<div id="main">
<h1></h1>
<div id="login">
<h2></h2>
<form action="login.php" method="post" name="login">
<label>UserName :</label>
<input id="name" name="username" placeholder="username" type="text">
<label>Password :</label>
<input id="password" name="password" placeholder="**********" type="password">
<input name="submit" type="submit" value=" Login ">
<span>'.$error.'</span>
</form>
</div>
</div>
</body>
</html>';
?>