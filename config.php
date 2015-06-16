
<?php
/*
    This file is part of Antivirus DV.

    Antivirus DV is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Antivirus DV is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Foobar.  If not, see <http://www.gnu.org/licenses/>.
*/
$sql_server="localhost";
$sql_user="root";
$sql_pass="admin";
$sql_db="db_virus";
$cert="dv.e";

$link=mysqli_connect($sql_server, $sql_user, $sql_pass);
if (!$link){
	die('Could not connect: ' . mysqli_error($link));
}

mysqli_select_db($link,$sql_db);


?>
