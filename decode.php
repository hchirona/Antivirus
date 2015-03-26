<?php
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
