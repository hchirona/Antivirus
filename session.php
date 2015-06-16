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
require('config.php');
session_start();
$user_check=$_SESSION['iduser'];
$ses_sql=mysqli_query($link,"select * from usuario where iduser='$user_check'");
$row = mysqli_fetch_assoc($ses_sql);
$login_session =$row['iduser'];
$userData= mysqli_fetch_array($ses_sql);
if(!isset($login_session)){
	mysqli_close($link);
	header('Location: index.php');
}
?>