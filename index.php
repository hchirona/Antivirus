<html>
    <head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Antivirus Digital Value</title>
        <link href="css/bootstrap.css" rel="stylesheet">
        <style type="text/css">
            h1 {
                font-family: arial;
                text-align: center;
                padding-top: 10px;
                color: #000000;
            }

            .scanner {
                padding-top: 130px;
                padding-bottom: 20px;
                background: #FFFFFF;
            }

            .scanner p{
                font-family: arial;
                padding-left: 40%;
                margin: 0;
                font-size: 15px;
            }

            .scanner button{
                font-family: arial;
                margin-left: 40%;
                font-size: 15px;
            }

            .g {
                color: #009900;
                font-weight: bold;
            }

            .r {
                color: #990000;
                font-weight: bold;
            }

            .r2 {
                color: #E78034;
            }

            .d {
                color: #AA9797;
            }

            .summary {
                padding-top: 10px;
                margin-top: -10px;
                background: #E3E2E0;
            }

            .summary p {
                font-size: 12px;
                text-align: center;
                padding: 5px;
                color: #000000;
            }

            .top-labels {
                background: #990033;
            }

            .top-labels p {
                padding: 10px 0px 20px 0px;
                text-align:center;
                color: #000000;
            }

            html{
                background: #E3E2E0;
            }
        </style>
        <title>Panel de acceso</title>
    </head>
    <body>
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
echo '<div class="summary"><center><h1>Acceso<br></h1>
<form action="index.php" method="post" name="session"><center><table border="0">
<tr><td>Usuario:</td><td><input id="name" name="username" type="text" required><br></td></tr>
<tr><td>Contraseña:</td><td><input id="password" name="password" type="password" required><br><td></tr>
<tr><td><input name="login" type="submit" value="Login"></td></tr>
</table></center><span>'.$error.' </span></form>';
} 
?>