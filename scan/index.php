<?php
/*
//   -------------------------------------------------------------------------------
//  |                  Antivirus DV: Malware scan software                          |
//  |              Copyright (c) 2015 by Héctor Chirona Torrentí                    |
//  |                                                                               |
//   -------------------------------------------------------------------------------
*/


header("Content-Type: text/plain");

$find= shell_exec("perl ./findbot-plus.pl /");
echo $find;

?>
