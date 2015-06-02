<?php
header("Content-Type: text/plain");

$find= shell_exec("perl ./findbot-plus.pl /");
echo $find;

?>
