<?php

error_reporting(0);

///////////////////////////////////////////////////////////////////////////////////////////////////Funciones que se utilizan

//DECODIFICACIÓN DE LOS FICHEROS
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

//MODIFICACION Y ESTRUCTURACION DE LA WEB (HTML, CSS, JAVASCRIPT, JQUERY...)
function renderhead() {
?>

<html>
    <head>
        <title>Código del archivo</title>

        <link href="css/bootstrap.css" rel="stylesheet">

        <style type="text/css">
            h1 {
                font-family: arial !important;
                text-align: center !important;
                padding: 10px 0 10px 0 !important;
            }

            .code{
                background-color:#000000 !important;
                resize:vertical !important;
                color:#00FF00 !important;
                padding:10px !important;
                overflow:auto !important;
                max-height:500px !important;
                min-height:300px !important;
                margin-top:10px !important;
            }

            body {
                padding-top: 10px !important;
                margin-top: -10px !important;
                background: #E3E2E0 !important;
                color: #000000 !important;
                font-family: arial !important;
                font-size: 14px !important;
            }

            tbody{
                color: #FFFFFF !important;
                font-family: arial !important;
                font-size: 14px !important;
            }

            .top-labels {
                background: #990033 !important;
            }

            html{
                margin-top: -20px !important;
            }

            button{
                margin-top:20px !important;
            }
        </style>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <script src="js/bootstrap.js"></script>
        <script>
            $(document).ready(function() {
                var path = sessionStorage.getItem("path");
                var name = path.split('/').pop();
                var titulo = $("#prueba").text()+name;
                var titulo = titulo + '"';
                $("#prueba").text(titulo);

                var texto_original = $('#bloque1').text();

                $('#save_btn').click(function(){
                    if($('#bloque1').val()==texto_original)
                        alert('No se ha modificado el contenido del fichero, no se realizará el guardado.');
                    else
                        window.location = 'inspector.php?p=' + path + '&nc=' + $('#bloque1').val();
                });

                $('#delete_btn').click(function(){
                    window.location = 'index.php?r=' + path;
                });

                //alert('BLOQUE2!!!!!!!!!!!!!!!!!!!' + $('#bloque2').text() + ' ------------------------VAL' + $('#bloque2').val());
                //alert('BLOQUE3!!!!!!!!!!!!!!!!!!!' + $('#bloque3').text() + ' ------------------------VAL' + $('#bloque3').val());

                if($('#bloque2').text()==''){
                    $('#bloque2').text("No se ha podido decodificar mediante las técnicas disponibles.");
                    //$('#bloque3').prev().prev().remove();
                    //$('#bloque3').remove();
                    $('#texto_intento').show();
                }

                if($('#bloque3').text()==''){
                    $('#bloque3').text("El código no ha podido ejecutarse por alguna razón.");
                }
            });
        </script>

    </head>
    <body>
        <?php
}

///////////////////////////////////////////////////////////////////////////////////////////////////Código encargado de la edición de un fichero

$new_code = $_GET["nc"];

if($new_code!=''){

    $filename = htmlspecialchars($_GET["p"]);

    /*if(!is_writable($filename)){
        echo '<br>Not writable!';
        $shell_code = 'sudo chmod 666 ' . $filename;
        $x = shell_exec($shell_code);
        echo '<br><pre>' . $x . '</pre>';
    }
    */
    $new_text = htmlspecialchars($_GET["nc"]);
    file_put_contents($filename, $new_text);

    renderhead();

    echo '<h1 id="prueba" class="top-labels">Archivo "</h1>';
    echo '<h1>Código Original:</h1><br>';
    echo '<textarea id="bloque1" class="col-md-10 col-md-offset-1 code" style="margin-top=0px">';

    $filename = htmlspecialchars($_GET["p"]);

    $fp = file($filename);

    foreach ($fp as $num_línea => $fp) {
        echo htmlspecialchars($fp);
    }

    echo '</textarea><button id="save_btn" class="col-md-2 col-md-offset-3 btn btn-primary">Guardar Archivo</button><button id="delete_btn" class="col-md-2 col-md-offset-2 btn btn-danger">Eliminar Archivo</button><br>';

    echo '<h1 style="display:inline-block" class="col-md-6 col-md-offset-3">Código Limpio - Archivo modificado con éxito</h1><br>';
    echo '<a href="index.php"><button class="col-md-2 col-md-offset-5 btn btn-default">Volver Atrás</button></a>';
}

///////////////////////////////////////////////////////////////////////////////////////////////////Código encargado del mostrado y ejecución del fichero
else{

    renderhead();

    $filename = htmlspecialchars($_GET["p"]);

    $fp = file($filename);

    echo '<h1 id="prueba" class="top-labels">Archivo "</h1>';
    echo '<h1>Código Original:</h1><br>';
    echo '<textarea id="bloque1" class="col-md-10 col-md-offset-1 code" style="margin-top=0px">';

    foreach ($fp as $num_línea => $fp) {
        echo htmlspecialchars($fp);
    }

    echo '</textarea><button id="save_btn" class="col-md-2 col-md-offset-3 btn btn-primary">Guardar Archivo</button><button id="delete_btn" class="col-md-2 col-md-offset-2 btn btn-danger">Eliminar Archivo</button><br>';

    echo '<h1 style="display:inline-block" class="col-md-4 col-md-offset-4">Código Decodificado:</h1><br>';
    echo '<div id="bloque2" class="col-md-10 col-md-offset-1 code" style="min-height:0px">';

    $fp = file_get_contents($filename);

    $cadena="preg_replace";

    $php_code = str_replace("<?php","",$fp);
    $php_code = str_replace("?>","",$php_code);
    $php_code = str_replace("<?","",$php_code);
    $php_code = preg_replace("/GIF.*?\n/","",$php_code);
    $php_code = preg_replace('/\s+/', '', $php_code);
    $php_code = preg_replace("/.*eval/"," eval",$php_code);

    //echo '<div style="background-color:blue">' . $php_code . '</div><br>';

    $busqueda=strpos($php_code,$cadena);
    if($busqueda !== false){
        $segu = str_replace("eval","echo",$php_code);
        ob_start();
        eval($segu);
        $a = ob_get_clean();
        $text = stripcslashes(nl2br(htmlentities($a)));
        echo $text;
    }else {
        $segu = str_replace("eval","echo",$php_code);
        ob_start();
        eval($segu);
        $a = ob_get_clean();
        $text = stripcslashes(nl2br(htmlentities($a)));
        echo $text;
    }

    echo '</div>';

    echo '<div id="texto_intento" class="col-md-4 col-md-offset-1" style="display:none; padding-left:0px; margin-top:10px">Sin embargo, prueba a ejecutar en <a href="http://ddecode.com/phpdecoder/" style="text-decoration:none">http://ddecode.com/phpdecoder/</a> el código original.</div>';
    echo '<h1 style="display:inline-block" class="col-md-4 col-md-offset-4">Código Ejecutado:</h1><br>';
    echo '<div id="bloque3" class="col-md-10 col-md-offset-1 code" style="max-height:800px; min-height:0px; margin-bottom:50px">';

    if($text!='' || $php_code!=''){
        ob_start();
        eval($php_code);
        ob_end_flush();
    }

    echo '</div>';
}

        ?>
    </body>
</html>


