<?php
header("Content-Type: text/plain");
$compresion=$_REQUEST["compresion"];
$codificacion=$_REQUEST["codificacion"];
$codigo=$_REQUEST["var"];

function limpiar_codigo($str){
$maximo= strlen ($str);
$ide= "<?";
$ide2= "?>";
$total= strpos($str,$ide);
$total2= stripos($str,$ide2);
$total3= ($maximo-$total2);
$final= substr ($str,$total+2,-$total3);
return $final;
}

function limpia_espacios($cadena){
$cadena = str_replace(' ', '', $cadena);
return $cadena;
}

function preg_decode($texto){
	$l1= preg_replace('/preg_replace\(\"\/\.\*\/e\"\,\"/', '', $texto);
	$l2= preg_replace('/\'.*/', '', $l1);
	$l3= 'print "'.$l2.'";';
	$l4= preg_replace('/^.*\'/' ,'', $l1);
	$l5= preg_replace('/\".*/' ,'' ,$l4);
	$l6= 'print "'.$l5.'";';
	$l7= preg_replace('/^.*?\'/','"',$l1);
	$l8= preg_replace('/\'.*/','"',$l7);
	ob_start();
	echo eval($l3);
	echo "$l8";
	echo eval($l6);
	$resultado=ob_get_clean();
	return $resultado;
}
$cadena="preg_replace";
if ($compresion=="" && $codificacion==""){
	$busqueda=strpos($codigo,$cadena);
		if($busqueda !== false){
			$codigo=preg_decode($codigo);
			$segu=str_replace("eval","echo",$codigo);
			$segu=str_replace("<?","",$segu);
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
