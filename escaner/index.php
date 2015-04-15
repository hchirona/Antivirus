<?php
ini_set('max_execution_time',0);

error_reporting(0);

////////////////////////////////////////////////////////////////////////////////////////////////////Conectar BD (Poner Base de Datos Final)

// Conectando, seleccionando la base de datos
//$link = mysqli_connect("localhost", "root", "") or die('No se pudo conectar: ' . mysqli_error());
//echo 'Connected successfully';
//mysqli_select_db($link,"cuarentena") or die('No se pudo seleccionar la base de datos');


///////////////////////////////////////////////////////////////////////////////////////////////////Configuración Inicial

$CONFIG = Array();
$CONFIG['debug'] = 0;
$CONFIG['scanpath'] = $_SERVER['DOCUMENT_ROOT'];
$CONFIG['extensions'] = Array();

@include("config.php");

if (!check_defs('virus.def'))
    trigger_error("Sobrescritura vulnerable en la base de datos de virus, porfavor cambia los permisos.", E_USER_ERROR);

if (!check_defs('signatures.def'))
    trigger_error("Sobrescritura vulnerable en la base de datos de virus, porfavor cambia los permisos.", E_USER_ERROR);

///////////////////////////////////////////////////////////////////////////////////////////////////Inicialización de variables

$report = '';
$dircount = 0;
$filecount = 0;
$infected = 0;
$new_log = 0;

///////////////////////////////////////////////////////////////////////////////////////////////////Elimina el fichero seleccionado

$file_to_remove = $_GET["r"];

if($file_to_remove!=''){

    $file_to_remove = htmlspecialchars($_GET["r"]);
    $shell_code = 'rm -f ' . $file_to_remove;
    shell_exec($shell_code);

}

///////////////////////////////////////////////////////////////////////////////////////////////////Funciones que se utilizan


// ESCANEADO DE FICHEROS
function file_scan($folder, $defs, $signatures, $debug) {
    global $dircount, $report;
    $dircount++;
    if ($debug)
        $report .= "<p class=\"d\">Escaneando carpeta $folder ...</p>";
    if ($d = @dir($folder)) {
        while (false !== ($entry = $d->read())) {
            $isdir = @is_dir($folder.'/'.$entry);
            if (!$isdir and $entry!='.' and $entry!='..' and stripos($folder,'cuarentena')==false) {
                virus_check($folder.'/'.$entry,$defs,$signatures,$debug);
            } elseif ($isdir  and $entry!='.' and $entry!='..') {
                file_scan($folder.'/'.$entry,$defs,$signatures,$debug);
            }
        }
        $d->close();
    }
}

// COMPROBACIÓN DE VIRUS/EXPRESIONES REGULARES
function virus_check($file, $defs, $signatures ,$debug) {
    global $filecount, $infected, $report, $CONFIG, $clean_files, $new_log, $link;

    $scannable = 0;
    foreach ($CONFIG['extensions'] as $ext) {
        if (substr($file,-3)==$ext)
            $scannable = 1;
    }

    if(basename($file) == "log.txt")
        $scannable = 0;

    if ($scannable) {
        $filecount++;
        $data = file($file);
        $data = implode('\r\n', $data);
        $clean = 1;
        $print = 1;
        $file_infected = 0;

        // Comparación contra: Base de Datos de Virus
        for ($i = 0; $i < sizeof($defs); $i++) {
            $pos = stripos($data, trim($defs[$i][1]));

            if ($pos !== false ){

                $report .= '<p class="r">Infectado: ' . $file . ' (' . $defs[$i][0] . ')</p>';
                if($new_log==1)
                    shell_exec('echo "Infectado: ' . $file . '" >> log.txt');

                $report .= '<p class="r2">Cadena comprometida--> "' . trim($defs[$i][1]) . '" en la posición: ' . $pos . '</p>';
                if($new_log==1)
                    shell_exec('echo "Cadena comprometida--> ' . trim($defs[$i][1]) . ' en la poisición: ' . $pos . '" >> log.txt');

                $fecha = New DateTime();
                date_timestamp_set($fecha, filemtime($file));

                $report .= '<p class="r2">Última fecha modificación: ' . date_format($fecha, 'd-m-Y H:i:s') . '</p>';
                if($new_log==1)
                    shell_exec('echo "Última fecha modificación: ' . date_format($fecha, 'd-m-Y H:i:s') . '" >> log.txt');

                $owner = posix_getpwuid(fileowner($file));
                $permisos = fileperms($file);

                // Formatear permisos
                if (($permisos & 0xC000) == 0xC000) {
                    $info = 's';
                } elseif (($permisos & 0xA000) == 0xA000) {
                    $info = 'l';
                } elseif (($permisos & 0x8000) == 0x8000) {
                    $info = '-';
                } elseif (($permisos & 0x6000) == 0x6000) {
                    $info = 'b';
                } elseif (($permisos & 0x4000) == 0x4000) {
                    $info = 'd';
                } elseif (($permisos & 0x2000) == 0x2000) {
                    $info = 'c';
                } elseif (($permisos & 0x1000) == 0x1000) {
                    $info = 'p';
                } else {
                    $info = 'u';
                }

                $info .= (($permisos & 0x0100) ? 'r' : '-');
                $info .= (($permisos & 0x0080) ? 'w' : '-');
                $info .= (($permisos & 0x0040) ?
                          (($permisos & 0x0800) ? 's' : 'x' ) :
                          (($permisos & 0x0800) ? 'S' : '-'));

                $info .= (($permisos & 0x0020) ? 'r' : '-');
                $info .= (($permisos & 0x0010) ? 'w' : '-');
                $info .= (($permisos & 0x0008) ?
                          (($permisos & 0x0400) ? 's' : 'x' ) :
                          (($permisos & 0x0400) ? 'S' : '-'));

                $info .= (($permisos & 0x0004) ? 'r' : '-');
                $info .= (($permisos & 0x0002) ? 'w' : '-');
                $info .= (($permisos & 0x0001) ?
                          (($permisos & 0x0200) ? 't' : 'x' ) :
                          (($permisos & 0x0200) ? 'T' : '-'));

                $report .= '<p class="r2">Permisos: ' . substr(sprintf("%o",fileperms($file)),-4) . ' ' . $info . '</p>';
                if($new_log==1)
                    shell_exec('echo "Permisos: ' . substr(sprintf("%o",fileperms($file)),-4) . ' ' . $info . '" >> log.txt');
                $report .= '<p class="r2">Propietario: ' . $owner['name'] . '</p>';
                if($new_log==1)
                    shell_exec('echo "Propietario: ' . $owner['name'] . '\n" >> log.txt');
                $report .= '<a id="' . $file . '"><button>Ver Código</button></a><br><br>';

                $file_infected = 1;

                $clean = 0;
            }
        }

        // Comparación contra: Expresiones regulares
        for ($i = 0; $i < sizeof($signatures); $i++) {
            $matches = preg_match(trim($signatures[$i][1]),$data);

            if ($matches !== 0){
                $report .= '<p class="r">Código malicioso: ' . $file . ' (' . $signatures[$i][0] . ')</p>';
                if($new_log==1)
                    shell_exec('echo "Código malicioso detectado: ' . $file . '\n" >> log.txt');
                $file_infected = 1;
                $clean = 0;
            }

        }

        if (($debug)&&($clean))
            $report .= '<p class="g">Limpio: ' . $file . '</p>';

        if($file_infected == 1){
            $md5=shell_exec('md5sum '.$file.'| cut -d " " -f -1');
            $nombre=basename($file);

            $nombre=trim($nombre);
            //$md5=trim($md5);
            $file=trim($file);

            //$sql="INSERT IGNORE INTO archivos (nombre, md5, ruta) VALUES ('".$nombre."','".$md5."','".$file."')";
            //$resultado=mysqli_query($link,$sql) or die ('Fallo al hacer Insert'.mysqli_error());
            if(is_dir('cuarentena')==false)
                mkdir('cuarentena',0777);
            copy($file,'cuarentena/'.$infected.'-'.$nombre);
            $infected++;
        }
    }
}

//CARGADO DE FICHEROS
function load_defs($file, $x, $debug) {

    $defs = file($file);
    $counter = 0;
    $counttop = sizeof($defs);

    while ($counter < $counttop) {
        $defs[$counter] = explode('	', $defs[$counter]);
        $counter++;
    }
    if ($debug && $x=="viruses")
        echo '<p>Consultados <strong>' . sizeof($defs) . '</strong> virus de la base de datos</p>';
    if ($debug && $x=="signatures")
        echo '<p>Consultadas <strong>' . sizeof($defs) . '</strong> heurística de la base de datos</p>';

    return $defs;
}

//COMPROBACION DE PERMISOS EN LAS BASES DE DATOS
function check_defs($file) {
    clearstatcache();
    $perms = substr(decoct(fileperms($file)),-2);

    /* ACORDARSE DE DESCOMENTAR ESTA SECCION, PARA LA COMPROBACIÓN DE CORRECTOS PERMISOS EN EL VIRUS.DEF ETC.
	if ($perms > 55)
		return false;
	else
		return true;
	*/

    return true;
}

//MODIFICACION Y ESTRUCTURACION DE LA WEB (HTML, CSS, JAVASCRIPT, JQUERY...)
function renderhead() {
?>
<html>
    <head>
        <title>Virus scan</title>
        <link href="css/bootstrap.css" rel="stylesheet">
        <style type="text/css">
            h1 {
                font-family: arial;
                text-align: center;
                padding-top: 10px;
                color: #000000;
            }

            .scanner {
                padding-top: 20px;
                padding-bottom: 20px;
                background: #E3E2E0;
            }

            .scanner p{
                font-family: arial;
                padding-left: 40%;
                margin: 0;
                font-size: 15px;
            }

            .scanner button{
                font-family: arial;
                margin-left: 40%;
                font-size: 15px;
            }

            .g {
                color: #009900;
                font-weight: bold;
            }

            .r {
                color: #990000;
                font-weight: bold;
            }

            .r2 {
                color: #E78034;
            }

            .d {
                color: #AA9797;
            }

            .summary {
                padding-top: 10px;
                margin-top: -10px;
                background: #E3E2E0;
            }

            .summary p {
                font-size: 12px;
                text-align: center;
                padding: 5px;
                color: #000000;
            }

            .top-labels {
                background: #990033;
            }

            .top-labels p {
                padding: 10px 0px 20px 0px;
                text-align:center;
                color: #000000;
            }

            html{
		background-color: #E3E2E0;
                margin-top: -20px;
            }
        </style>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <script src="js/bootstrap.js"></script>
        <script>
            $(document).ready(function() {
                $('a').each(function(){
                    var x = $(this).attr('id');
                    $(this).attr('href','inspector.php?p='+x);
                    $(this).on("click", function(){
                        sessionStorage.path = x;
                    });
                });
            });
        </script>
    </head>
    <body>
        <?php
}

///////////////////////////////////////////////////////////////////////////////////////////////////Funcionalidad del escáner de amenazas

renderhead();

echo '<div class="top-labels">';
echo '<h1>Escaneo Completado</h1>';

$defs = load_defs('virus.def', "viruses", $CONFIG['debug']);
$signatures = load_defs('signatures.def', "signatures", $CONFIG['debug']);

//Comprobar si el fichero de log tiene mas de 7 dias y borrarlo

$today_ts = time();
$log_ctime = shell_exec("stat -c '%Z' log.txt");

$diff_sec = $today_ts - $log_ctime;
$diff_days = $diff_sec / (60 * 60 * 24);
$diff_days = abs($diff_days);
$diff_days = floor($diff_days);

if($diff_days>=1){
    shell_exec("rm -f log.txt");
    $new_log = 1;
}
//

file_scan($CONFIG['scanpath'], $defs, $signatures, $CONFIG['debug']);

echo '</div>';
echo '<div class="summary col-md-12">';
echo '<p><strong>Carpetas escaneadas:</strong> ' . $dircount . '</p>';
echo '<p><strong>Ficheros escaneados:</strong> ' . $filecount . '</p>';
echo '<p style="color:red"><strong>Ficheros infectados:</strong> ' . $infected . '</p>';
echo '</div>';
echo '<div class="scanner">';
echo $report;

        ?>
    </body>
</html>
