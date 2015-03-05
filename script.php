<?php
header("Content-Type: text/plain");
$valido=$_REQUEST['valido'];
if($valido){
$salida=shell_exec('ls -l .');
echo "$salida";
}
?>
