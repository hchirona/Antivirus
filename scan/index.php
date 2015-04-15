<?php
header("Content-Type: text/plain");

$find= shell_exec("perl ./findbot.pl /var/www/malware");
echo $find;

?>
