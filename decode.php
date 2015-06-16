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
require('func.php');
header("Content-Type: text/plain");
$compresion=$_REQUEST["compresion"];
$codificacion=$_REQUEST["codificacion"];
$codigo=$_REQUEST["var"];
$cadena="preg_replace";

if ($compresion=="" && $codificacion==""){
	$busqueda=strpos($codigo,$cadena);
	if($busqueda !== false){
		$codigo=preg_decode($codigo);
		$segu=str_replace("eval","echo",$codigo);
		$segu=str_replace("<?php","",$segu);
		$segu=str_replace("?>","",$segu);
		eval($segu);
	}else {
		$segu=str_replace("eval","echo",$codigo);
		$segu=str_replace("<?php","",$segu);
		$segu=str_replace("?>","",$segu);
		eval($segu);
	}

}elseif ($compresion!=="" && $codificacion!==""){
	$deco=$compresion($codificacion($codigo));
	echo $deco;
}elseif ($compresion!=="" && $codificacion=="") {
	$deco=$compresion($codigo);
	echo $deco;
}elseif ($compresion=="" && $codificacion!=="") {
	$deco=$codificacion($codigo);
	echo $deco;
}else {
	echo "error al introducir los datos";
};

?>
