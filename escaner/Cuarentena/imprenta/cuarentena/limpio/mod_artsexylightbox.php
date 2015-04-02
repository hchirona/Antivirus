<?php
/*
All Rights Reserved.  

No use, copying or distribution of this work may be made except in accordance with a valid license agreement from Artetics. 
This notice must be included on all copies, modifications and derivatives of this work. Artetics products are provided "as is" without warranty of any kind, 
either expressed or implied. In no event shall our juridical person be liable for any damages including, but not limited to, direct, indirect, special, incidental or 
consequential damages or other losses arising out of the use of or inability to use our products.

Customer may not sub-license, assign, or transfer this license to anyone else without prior written consent from Artetics.

All files and subfolders in this folder are provided under the license mentioned above with exception for files that contain license notes themselves.

Copyright (c) 2009 Artetics, artetics.com
*/

error_reporting(E_ERROR);
define ("DS", DIRECTORY_SEPARATOR);
require_once(JPATH_SITE . DS . 'modules' . DS . 'mod_artsexylightbox' . DS . 'artsexylightbox' . DS . 'library' . DS . 'json.php');
require_once(JPATH_SITE . DS . 'modules' . DS . 'mod_artsexylightbox' . DS . 'artsexylightbox' . DS . 'library' . DS . 'asido' . DS . 'class.asido.php');

if (!function_exists('aslMisImage')) {
	/** Check whether file is an image **/
	function aslMisImage($fileName) {
		$extensions = array('.jpeg', '.jpg', '.gif', '.png', '.bmp', '.tiff', '.tif', '.ico', '.rle', '.dib', '.pct', '.pict');
		$extension = substr($fileName, strrpos($fileName,"."));
		if (in_array(strtolower($extension), $extensions)) return true;
		return false;
	}
}
if (!function_exists('aslMisExtensionsLoaded')) {
	/** Check whether Asido extension is loaded **/
	function aslMisExtensionsLoaded($drivers = array('imagick', 'gd', 'magickwand')) {
		reset($drivers);

		foreach ($drivers as $driver) {
			if ($driver == 'imagick' && !function_exists('imagick_readImage')) continue;
			
			if (@extension_loaded($driver)) {
				return true;
			}
		}
		
		return false;
	}
}
if (!function_exists('aslMloadExtensions')) {
	/** Load Asido extensions **/
	function aslMloadExtensions($drivers = array('imagick', 'gd', 'magickwand')) {
		$driverAliases = array('imagick' => 'imagick_ext', 'magickwand' => 'magick_wand');
		reset($drivers);
		foreach ($drivers as $driver) {
			if (@extension_loaded($driver)) {
				if ($driver == 'imagick' && !function_exists('imagick_readImage')) continue ;
				
				if (array_key_exists($driver, $driverAliases)) $driver = $driverAliases[$driver];
				asido::driver($driver);
				return true;
			}
		}

		return false;
	}

}

aslMloadExtensions();

if (!function_exists('aslMartSLFileAscSort')) {
/** Sort images in ascending order **/
function aslMartSLFileAscSort($a, $b) {
	list ($anum, $aalph) = explode ('.', $a);
	list ($bnum, $balph) = explode ('.', $b);
	
	if ($anum == $bnum) return strcmp($aalph, $balph);
	return $anum < $bnum ? -1 : 1;
}
}
if (!function_exists('aslMartSLFileDescSort')) {
/** Sort files in descending order **/
function aslMartSLFileDescSort($a, $b) {
	list ($anum, $aalph) = explode ('.', $a);
	list ($bnum, $balph) = explode ('.', $b);
	
	if ($anum == $bnum) return !strcmp($aalph, $balph);
	return $anum > $bnum ? -1 : 1;
}
}

$finalParams = array();
$finalParams['path'] = $params->get('path','');
$finalParams['thumbnailPath'] = $params->get('thumbnailPath','');
$finalParams['name'] = $params->get('name','SLB');
$finalParams['zIndex'] = $params->get('zIndex',65555);
$finalParams['color'] = $params->get('color','black');
$finalParams['find'] = $params->get('find','sexylightbox');
$finalParams['imagesdir'] = $params->get('imagesdir',JURI::BASE() . 'modules/mod_artsexylightbox/artsexylightbox/images');
$finalParams['background'] = $params->get('background','bgSexy.png');
$finalParams['backgroundIE'] = $params->get('backgroundIE','bgSexy.gif');
$finalParams['closeButton'] = $params->get('closeButton','SexyClose.png');
$finalParams['displayed'] = $params->get('displayed',0);
$finalParams['modal'] = $params->get('modal',0);
$finalParams['showDuration'] = $params->get('showDuration',200);
$finalParams['showEffect'] = $params->get('showEffect','linear');
$finalParams['closeDuration'] = $params->get('closeDuration',400);
$finalParams['closeEffect'] = $params->get('closeEffect','linear');
$finalParams['moveDuration'] = $params->get('moveDuration',800);
$finalParams['resizeDuration'] = $params->get('resizeDuration',800);
$finalParams['moveEffect'] = $params->get('moveEffect','easeOutBack');
$finalParams['resizeEffect'] = $params->get('resizeEffect','easeOutBack');
$finalParams['noConflict'] = $params->get('noConflict',false);
$finalParams['previewWidth'] = $params->get('previewWidth','');
$finalParams['previewHeight'] = $params->get('previewHeight','');
$finalParams['width'] = $params->get('width','');
$finalParams['height'] = $params->get('height','');
if ($finalParams['width'] && $finalParams['height']) {
  $whStr = "?width=" . $finalParams['width'] . "&height=" . $finalParams['height'];
} else {
  $whStr = "";
}
$finalParams['contentType'] = $params->get('contentType',1);
$finalParams['loadJQuery'] = $params->get('loadJQuery',1);
$finalParams['downloadLink'] = $params->get('downloadLink',0);
$finalParams['autoGenerateThumbs'] = $params->get('autoGenerateThumbs',0);
$finalParams['convertImageOption'] = $params->get('convertImageOption','resize');
$cropStart = $params->get('cropStart',false);
$cropEnd = $params->get('cropEnd',false);
$finalParams['arotate'] = $params->get('arotate','');
$finalParams['width1'] = $params->get('width1',500);
$finalParams['height1'] = $params->get('height1',500);

if ($finalParams['convertImageOption'] == 1) {
  $finalParams['convertImage'] = $finalParams['convertImageOption'];
} else if ($finalParams['arotate']){
  $finalParams['convertImage'] = 'rotate:' . $finalParams['arotate'];
} else {
  $finalParams['convertImage'] = $finalParams['convertImageOption'];
}
$finalParams['imageDescriptions'] = $params->get('imageDescriptions','');

$finalParams['numberOfImages'] = $params->get('numberOfImages','');
$finalParams['caption'] = $params->get('caption',0);
$finalParams['singleOption'] = $params->get('singleOption','full');
$finalParams['singleContent'] = $params->get('singleContent','');
if ($finalParams['singleOption'] == 'singleImage') {
  $finalParams['singleImage'] = $finalParams['singleContent'];
} else if ($finalParams['singleOption'] == 'singleText') {
  $finalParams['singleText'] = $finalParams['singleContent'];
}
$finalParams['alt'] = $params->get('altTag','');
$finalParams['sort'] = $params->get('sort','asc');

$finalParams['singleImageOption'] = $params->get('singleImage','');
if ($finalParams['singleImageOption']) {
  $finalParams['singleImage'] = $finalParams['singleImageOption'];
}

$color = $finalParams['color'];
if (!$color) {
  $color = 'oldblack';
}

/** Dimensions **/
$previewWidth = $finalParams['previewWidth'];
$previewHeight = $finalParams['previewHeight'];

/** Path **/
$path = $finalParams['path'];
$thumbnailPath = $finalParams['thumbnailPath'];

/** Convert images **/
$autoGenerateThumbs = $finalParams['autoGenerateThumbs'];
$convertImage = $finalParams['convertImage'];

if (!$convertImage) {
  $convertImage = 'resize';
}

/** Other parameters **/
$noConflict = $finalParams['noConflict'];
$singleImage = $finalParams['singleImage'];
$showSingleImage = $finalParams['showSingleImage'];
$showSingleImage = 'false';
if ($finalParams['singleText']) {
  $finalParams['singleText'] = str_replace('[', '&#91;', $finalParams['singleText']);
  $finalParams['singleText'] = str_replace(']', '&#93;', $finalParams['singleText']);
}
$singleText = $finalParams['singleText'];
$thumbnailPreviewCount = $finalParams['thumbnailPreviewCount'];
$popup = $finalParams['popup'];
$numberOfImages = $finalParams['numberOfImages'];
$magnifier = $finalParams['magnifier'];
$sort = $finalParams['sort'];
$pAlt = $finalParams['alt'];
$caption = $finalParams['caption'];
$lang = $finalParams['lang'];

$imagesCode = '<div class="artsexylightbox_container"';
if ($cloudCarousel) {
  $imagesCode .= 'style="width:' . $containerWidth . ';height:' . $containerHeight . '" ';
}
$imagesCode .= '>';
		
/** Add resources **/
$document = &JFactory::getDocument();
if ($finalParams['loadJQuery']) {
  $document->addScript(JURI::root() . 'modules/mod_artsexylightbox/artsexylightbox/js/jquery.js');
}
$document->addScript(JURI::root() . 'modules/mod_artsexylightbox/artsexylightbox/js/jquery.easing.1.3.js');

if ($color == 'white' || $color == 'black') {
  $document->addScript(JURI::root() . 'modules/mod_artsexylightbox/artsexylightbox/js/script.v2.2.jquery.min.js');
  $document->addStyleSheet( JURI::root() . 'modules/mod_artsexylightbox/artsexylightbox/css/oldsexylightbox.css' );
} else {
  if ($color == 'spanish_white') {
    $color = 'blanco';
    $finalParams['color'] = 'blanco';
  } else if ($color == 'spanish_black') {
    $color = 'negro';
    $finalParams['color'] = 'negro';
  }
  $document->addScript(JURI::root() . 'modules/mod_artsexylightbox/artsexylightbox/js/script.v2.3.4.jquery.min.js');
  $document->addStyleSheet( JURI::root() . 'modules/mod_artsexylightbox/artsexylightbox/css/sexylightbox.css' );
}
$document->addScript(JURI::root() . 'modules/mod_artsexylightbox/artsexylightbox/js/jquery.nc.js');

/** Begin inline javascript code **/
$jsCode .= '<script type="text/javascript" charset="utf-8">asljQuery(function(){asljQuery(document).ready(function(){';

/** ID for gallery **/
$id = uniqid('artgallery_', false);
		
/** Encode input parameters **/
$imageDescrParam = $finalParams['imageDescriptions'];
$finalParams['imageDescriptions'] = '';
$jsonHandler = new ART_Services_JSON(SERVICES_JSON_LOOSE_TYPE);
$dFP = !empty($finalParams) ? $jsonHandler->encode($finalParams) : '';

if ($path) {
  if ($thumbnailPath) {
    $thumbs = array();
    $thumb_directory_stream = @ opendir (JPATH_SITE.DS . $thumbnailPath . DS);
    if ($thumb_directory_stream) {
      while ($entry = readdir ($thumb_directory_stream)) {
        if ($entry != '.' && $entry != '..' && aslMisImage($thumbnailPath . $entry)) {
          $thumbs[$entry] = $entry;
        }
      }
    }
  }
  /** Read image descriptions **/
  if ($lang && file_exists(JPATH_SITE . DS . $path . DS . 'artsexylightbox_' . $lang . '.txt')) {
    $file_handle = @fopen(JPATH_SITE . DS . $path . DS . 'artsexylightbox_' . $lang . '.txt', "rb");
  } else if (JRequest::getString('lang') && file_exists(JPATH_SITE . DS . $path . DS . 'artsexylightbox_' . JRequest::getString('lang') . '.txt')) {
    $file_handle = @fopen(JPATH_SITE . DS . $path . DS . 'artsexylightbox_' . JRequest::getString('lang') . '.txt', "rb");
  } else if ($this && $this->language && file_exists(JPATH_SITE . DS . $path . DS . 'artsexylightbox_' . $this->language . '.txt')) {
    $file_handle = @fopen(JPATH_SITE . DS . $path . DS . 'artsexylightbox_' . $this->language . '.txt', "rb");
  } else {
    $file_handle = @fopen(JPATH_SITE . DS . $path . DS . 'artsexylightbox.txt', 'rb');
  }
  $descriptionArray = array();
  if ($file_handle) {
    while (!feof($file_handle) ) {
      $line_of_text = fgets($file_handle);
      $parts = explode('=', htmlspecialchars($line_of_text, ENT_QUOTES));
      $str = '';
      $partsNumber = count($parts);
      for ($i = 1; $i < $partsNumber; $i++) {
        $str .= $parts[$i];
        if ($i != $partsNumber - 1) {
          $str .= '=';
        }
      }
      $str = str_replace('"', "'", $str);
      $descriptionArray[$parts[0]] = html_entity_decode($str);
    }
    fclose($file_handle);
  }

  $jsCode .= 'if (!window.sexylightboxEnabled) {SexyLightbox.initialize(' . $dFP . ');} if (!window.sexylightboxEnabled) {window.sexylightboxEnabled = true;}';
  $directory_stream = @ opendir (JPATH_SITE.DS . $path . DS); 
  if (!$directory_stream) {
    echo "Could not open a directory stream for <i>" . JPATH_SITE . DS . $path . DS . "</i>";
  }
  $filelist = array();
  while ($entry = readdir ($directory_stream)) {
    if ($entry != '.' && $entry != '..' && aslMisImage($path . $entry)) {
      $filelist[] = $entry;
    }
  }
  /** Sort images **/
  if ($sort == 'desc') {
    usort ($filelist, 'aslMartSLFileDescSort');
  } else {
    usort ($filelist, 'aslMartSLFileAscSort');
  }
  if ($thumbnailPreviewCount && ($thumbnailPreviewCount > count($filelist))) {
    $thumbnailPreviewCount = count($filelist);
  }
  if ($singleImage) {
    if ($singleImage == 'random') {
      mt_srand((double)microtime()*1000000);
      $rand = mt_rand(0, count($filelist) - 1);
      if (isset($descriptionArray[$filelist[$rand]])) {
        $title = $descriptionArray[$filelist[$rand]];
      }
      $singleImage = $path . '/' . $filelist[$rand];
    }
	  $title = '';
	  $si = $singleImage;
	  if (strrpos($singleImage, '/')) {
      $si = substr($singleImage, strrpos($singleImage, '/') + 1);
	  }
      if (isset($descriptionArray[$si])) {
        $title = $descriptionArray[$si];
      }
      $imagePath = JPATH_SITE . DS . str_replace('/', DS, $singleImage);
      if (@is_readable($imagePath) && ($previewWidth > 0 || $previewHeight > 0 || strstr($convertImage, 'rotate')) && isset($autoGenerateThumbs) && $autoGenerateThumbs != 0) {
        if (!aslMisExtensionsLoaded()) {
          aslMloadExtensions();
        }
        if (!aslMisExtensionsLoaded()) {
          $imagesCode .= "<a onclick='asljQuery(\"a[rel*=" . $id . "]\").eq(0).click();return false;'><img alt='artsexylightbox' class='artsexylightbox_singleimage' src='" . JURI::root() . $singleImage . "'";
        } else {
          $imagePathInfo = pathinfo($imagePath);
          $generatedThumbName = sprintf('%s_%s_%s.%s',
            md5('artsexythumb_' . $convertImage . $cropStart . $cropEnd . '_' . $imagePath),
            $previewWidth,
            $previewHeight,
            $imagePathInfo['extension']);
          if (!file_exists(JPATH_SITE . DS . 'images' . DS . 'artsexylightbox_tmp')) {
            mkdir (JPATH_SITE . DS . 'images' . DS . 'artsexylightbox_tmp');
          }
          $thumbPath = JPATH_SITE . DS . 'images' . DS . 'artsexylightbox_tmp' . DS . $generatedThumbName;
          
          if (!@file_exists($thumbPath)) {
            $img = asido::image($imagePath, $thumbPath);
            if ($convertImage == 'crop' && $previewWidth && $previewHeight) {
              if ($cropStart) {
                $cropStartArray = explode(',', $cropStart);
                if ($cropEnd) {
                  $cropEndArray = explode(',', $cropEnd);
                  Asido::crop($img, $cropStartArray[0], $cropStartArray[1], $cropEndArray[0], $cropEndArray[1]);
                } else {
                  Asido::crop($img, $cropStartArray[0], $cropStartArray[1], $previewWidth, $previewHeight);
                }
              } else {
                Asido::crop($img, 0, 0, $previewWidth, $previewHeight);
              }
            } else if ($convertImage == 'crop_resize' && $previewWidth && $previewHeight) {
              $imgSize = getimagesize($imagePath);
              $imgWidth = $imgSize[0];
              $imgHeight = $imgSize[1];
              if ($imgWidth > $imgHeight) {
                $diff = ($imgWidth - $imgHeight)/2;
                if ($cropStart) {
                  $cropStartArray = explode(',', $cropStart);
                  if ($cropEnd) {
                    $cropEndArray = explode(',', $cropEnd);
                    Asido::crop($img, $cropStartArray[0], $cropStartArray[1], $cropEndArray[0], $cropEndArray[1]);
                  } else {
                    Asido::crop($img, $cropStartArray[0], $cropStartArray[1], $previewWidth, $previewHeight);
                  }
                } else {
                  Asido::crop($img, 0, 0, $previewWidth, $previewHeight);
                }
              } else if ($imgWidth < $imgHeight) {
                $diff = ($imgHeight - $imgWidth)/2;
                if ($cropStart) {
                  $cropStartArray = explode(',', $cropStart);
                  Asido::crop($img, $cropStartArray[0], $cropStartArray[1], $previewWidth, $previewHeight);
                } else {
                  Asido::crop($img, 0, 0, $previewWidth, $previewHeight);
                }
              }
              Asido::resize($img, $previewWidth, $previewHeight, ASIDO_RESIZE_STRETCH);
            } else if (strstr($convertImage, 'rotate')) {
              $rotateAttrs = explode(':', $convertImage);
              if (!$rotateAttrs[1]) {
                $rotateAttrs[1] = 90;
              }
              Asido::Rotate($img, $rotateAttrs[1]);
            } else {
              if (!$previewWidth) {
                Asido::height($img, $previewHeight);
              } else if (!$previewHeight) {
                Asido::width($img, $previewWidth);
              } else {
                Asido::resize($img, $previewWidth, $previewHeight, ASIDO_RESIZE_STRETCH);
              }
            }
            $img->save(ASIDO_OVERWRITE_ENABLED);
          }
          $imagesCode .= "<a onclick='asljQuery(\"a[rel*=" . $id . "]\").eq(0).click();return false;'><img alt='artsexylightbox' class='artsexylightbox_singleimage' src='" . JURI::root() . "images/artsexylightbox_tmp/$generatedThumbName' ";
        }
      } else {
        $imagesCode .= "<a onclick='asljQuery(\"a[rel*=" . $id . "]\").eq(0).click();return false;'><img alt='artsexylightbox' class='artsexylightbox_singleimage' src='" . JURI::root() . $singleImage . "'";
      }
      if ($previewHeight) {
        $imagesCode .= " height='$previewHeight'";
      }
      if ($previewWidth) {
        $imagesCode .= " width='$previewWidth' ";
      }
      $imagesCode .= " /></a>";
	  if ($caption) {
	    $title = str_replace('&lt;', '<', $title);
	    $title = str_replace('&gt;', '>', $title);
	    $imagesCode .= '<div class="asl_caption">' . html_entity_decode($title) . '</div>';
	    $imagesCode .= '</div>';
	  }
    
  } else if ($singleText) {
    $imagesCode .= "<a onclick='asljQuery(\"a[rel*=" . $id . "]\").eq(0).click();return false;'><span class='artsexylightbox_singletext'>" . $singleText . "</span></a>";
  } else if ($thumbnailPreviewCount && $thumbnailPreviewCount > 0) {
    $thumbsCount = 0;
    while ((list ($key, $entry) = each ($filelist)) && ($thumbsCount < $thumbnailPreviewCount)) {
      if ($entry != '.' && $entry != '..' && aslMisImage($path . $entry)) {
        $imagePath = JPATH_SITE . DS . $path . DS . $entry;
        if ($thumbs && isset($thumbs[$entry])) {
          $imagesCode .= "<a onclick='asljQuery(\"a[rel*=" . $id . "]\").eq(0).click();return false;'><img alt='artsexylightbox' class='artsexylightbox_singleimage' src='" . JURI::root() . $thumbnailPath . '/' . $entry . "?width=" . $width . "&height=" . $height . "'";
        } else {
          $imagesCode .= "<a onclick='asljQuery(\"a[rel*=" . $id . "]\").eq(0).click();return false;'><img alt='artsexylightbox' class='artsexylightbox_singleimage' src='" . JURI::root() . $path . '/' . $entry . "'";
        }
        if ($previewHeight) {
          $imagesCode .= " height='$previewHeight'";
        }
        if ($previewWidth) {
          $imagesCode .= " width='$previewWidth' ";
        }
        $imagesCode .= " /></a>";
        $thumbsCount++;
      }
    }
  }
  reset ($filelist);
  $imagesCount = 0; 
  if ($singleImage) {
    $nfilelist = array();
    if ($showSingleImage != 'false') {
      if ($singleImage != 'random') {
        $nfilelist[] = $singleImage;
      } else {
        $nfilelist[] = $path. '/' .$filelist[$rand];
      }
    }
    while ((list ($key, $entry) = each ($filelist))) {
      if (($path . '/' . $entry) != $singleImage) {
        $nfilelist[] = $entry;
      }
    }
    reset ($nfilelist);
    $filelist = $nfilelist;
  }
        
  /** Show gallery from local folder **/
  $o = 0;
  while (list ($key, $entry) = each ($filelist)) {
    if ($entry != '.' && $entry != '..' && aslMisImage($path . $entry)) {
      $title = '';
      if (isset($descriptionArray[$entry])) {
        $title = $descriptionArray[$entry];
      }
      if ($caption) {
        $imagesCode .= "<div class='asl_image_caption'>";
      }
      if ($singleImage && $o == 0 && $showSingleImage != 'false') {
        $ind1 = strrpos($singleImage, '/');
        $sI = substr($singleImage, $ind1 + 1);
        if (isset($descriptionArray[$sI])) {
          $title = $descriptionArray[$sI];
        }
        $imagesCode .= "<a href='" . JURI::root() . "$entry" . $whStr;
        $imagesCode .= "' rel='sexylightbox[" . $id . "]' class='artsexylightboxpreview' ";
      } else {
        $imagesCode .= "<a href='" . JURI::root() . "$path/$entry" . $whStr;
        $imagesCode .= "' rel='sexylightbox[" . $id . "]' class='artsexylightboxpreview' ";
      }
      $o++;
      if ($title) {
        $imagesCode .= " title='" . $title . "'";
      }
      if ($singleImage || (isset($numberOfImages) && $numberOfImages > 0 && ($o > $numberOfImages))) {
          $imagesCode .= " style='display:none;'></a>";
          if ($caption) {
            $imagesCode .= '</div>';
          }
      } else {
        $imagesCode .= ">";
        if ($pAlt) {
          $alt = $pAlt;
        } else if ($title) {
          $alt = $title;
        } else if ($path) {
          $ind = strrpos($path, '/');
          $alt = substr($path, $ind + 1);
        } else {
          $alt = 'artsexylightbox';
        }
        $imagesCode .= "<img alt='" . $alt . "' class='artsexylightbox' ";
        if ($thumbnailPreviewCount || $singleText) {
          $imagesCode .= " style='display:none;'";
        }
        $imagesCode .= " src='"; 
        $imagePath = JPATH_SITE . DS . $path . DS . $entry;
        if ($thumbs && isset($thumbs[$entry])) {
          $imagesCode .= JURI::root() . "$thumbnailPath/$entry";
          $imagesCode .= "' ";
        } else if (@is_readable($imagePath) && ($previewWidth > 0 || $previewHeight > 0 || strstr($convertImage, 'rotate')) && isset($autoGenerateThumbs) && $autoGenerateThumbs != 0) {
          if (!aslMisExtensionsLoaded()) {
            aslMloadExtensions();
          }
          if (!aslMisExtensionsLoaded()) {
            $imagesCode .= JURI::root() . "$path/$entry' ";
          } else {
            $imagePathInfo = pathinfo($imagePath);
            $generatedThumbName = sprintf('%s_%s_%s.%s',
              md5('artsexythumb_' . $convertImage . $cropStart . $cropEnd . '_' . $imagePath),
              $previewWidth,
              $previewHeight,
              $imagePathInfo['extension']);
            if (!file_exists(JPATH_SITE . DS . 'images' . DS . 'artsexylightbox_tmp')) {
              mkdir (JPATH_SITE . DS . 'images' . DS . 'artsexylightbox_tmp');
            }
            $thumbPath = JPATH_SITE . DS . 'images' . DS . 'artsexylightbox_tmp' . DS . $generatedThumbName;
            
            if (!@file_exists($thumbPath)) {
              $img = asido::image($imagePath, $thumbPath);
              if ($convertImage == 'crop' && $previewWidth && $previewHeight) {
                if ($cropStart) {
                  $cropStartArray = explode(',', $cropStart);
                  if ($cropEnd) {
                    $cropEndArray = explode(',', $cropEnd);
                    Asido::crop($img, $cropStartArray[0], $cropStartArray[1], $cropEndArray[0], $cropEndArray[1]);
                  } else {
                    Asido::crop($img, $cropStartArray[0], $cropStartArray[1], $previewWidth, $previewHeight);
                  }
                } else {
                  Asido::crop($img, 0, 0, $previewWidth, $previewHeight);
                }
              } else if ($convertImage == 'crop_resize' && $previewWidth && $previewHeight) {
                $imgSize = getimagesize($imagePath);
                $imgWidth = $imgSize[0];
                $imgHeight = $imgSize[1];
                if ($imgWidth > $imgHeight) {
                  $diff = ($imgWidth - $imgHeight)/2;
                  if ($cropStart) {
                    $cropStartArray = explode(',', $cropStart);
                    if ($cropEnd) {
                      $cropEndArray = explode(',', $cropEnd);
                      Asido::crop($img, $cropStartArray[0], $cropStartArray[1], $cropEndArray[0], $cropEndArray[1]);
                    } else {
                      Asido::crop($img, $cropStartArray[0], $cropStartArray[1], $previewWidth, $previewHeight);
                    }
                  } else {
                    Asido::crop($img, 0, 0, $previewWidth, $previewHeight);
                  }
                } else if ($imgWidth < $imgHeight) {
                  $diff = ($imgHeight - $imgWidth)/2;
                  if ($cropStart) {
                    $cropStartArray = explode(',', $cropStart);
                    Asido::crop($img, $cropStartArray[0], $cropStartArray[1], $previewWidth, $previewHeight);
                  } else {
                    Asido::crop($img, 0, 0, $previewWidth, $previewHeight);
                  }
                }
                Asido::resize($img, $previewWidth, $previewHeight, ASIDO_RESIZE_STRETCH);
              } else if (strstr($convertImage, 'rotate')) {
                $rotateAttrs = explode(':', $convertImage);
                if (!$rotateAttrs[1]) {
                  $rotateAttrs[1] = 90;
                }
                Asido::Rotate($img, $rotateAttrs[1]);
              } else {
                if (!$previewWidth) {
                  Asido::height($img, $previewHeight);
                } else if (!$previewHeight) {
                  Asido::width($img, $previewWidth);
                } else {
                  Asido::resize($img, $previewWidth, $previewHeight, ASIDO_RESIZE_STRETCH);
                }
              }
              $img->save(ASIDO_OVERWRITE_ENABLED);
            }
            $imagesCode .= JURI::root() . "images/artsexylightbox_tmp/$generatedThumbName' ";
          }
        } else {
          $imagesCode .= JURI::root() . "$path/$entry' ";
        }
        if ($previewHeight) {
          $imagesCode .= " height='$previewHeight' ";
        }
        if ($previewWidth) {
          $imagesCode .= "width='$previewWidth' ";
        }
        $imagesCode .= "/></a>";
        if ($caption) {
          $title = str_replace('&lt;', '<', $title);
          $title = str_replace('&gt;', '>', $title);
          $imagesCode .= '<div class="asl_caption">' . html_entity_decode($title) . '</div>';
          $imagesCode .= '</div>';
        }
      }
    }
    $imagesCount++;
  }
  if (isset($popup) && $popup == 'false') {
    echo $imagesCode;
  }
  $jsCode .= '})});</script>';
  $imagesCode .= '</div>';
  echo $imagesCode.$jsCode;
}

?>