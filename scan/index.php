<?php
header("Content-Type: text/plain");

$find= shell_exec("perl ./findbot.pl ../");
echo $find;

?>
