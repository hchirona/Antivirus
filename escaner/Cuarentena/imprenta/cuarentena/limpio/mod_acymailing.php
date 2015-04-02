<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	4.2.0
 * @author	acyba.com
 * @copyright	(C) 2009-2013 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

if(!include_once(rtrim(JPATH_ADMINISTRATOR,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acymailing'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')){
	echo 'This module can not work without the AcyMailing Component';
	return;
};

$doc = JFactory::getDocument();
$config = acymailing_config();

switch($params->get('redirectmode','0')){
	case 1 :
		$redirectUrl = acymailing_completeLink('lists',false,true);
		$redirectUrlUnsub = $redirectUrl;
		break;
	case 2 :
		$redirectUrl = $params->get('redirectlink');
		$redirectUrlUnsub = $params->get('redirectlinkunsub');
		break;
	default :
		if(isset($_SERVER["REQUEST_URI"])){
			$requestUri = $_SERVER["REQUEST_URI"];
		}else{
		$requestUri = $_SERVER['PHP_SELF'];
		if (!empty($_SERVER['QUERY_STRING'])) $requestUri = rtrim($requestUri,'/').'?'.$_SERVER['QUERY_STRING'];
		}
	$redirectUrl = ((empty($_SERVER['HTTPS']) OR strtolower($_SERVER['HTTPS']) != "on" ) ? 'http://' : 'https://').$_SERVER["HTTP_HOST"].$requestUri;
	$redirectUrlUnsub = $redirectUrl;
	if($params->get('effect','normal') == 'mootools-box') $redirectUrlUnsub = $redirectUrl = '';
}

$regex = trim(preg_replace('#[^a-z0-9\|\.]#i','',$config->get('module_redirect')),'|');
if($regex != 'all'){
	preg_match('#^(https?://)?(www.)?([^/]*)#i',$redirectUrl,$resultsurl);
	$domainredirect = preg_replace('#[^a-z0-9\.]#i','',@$resultsurl[3]);
	preg_match('#^(https?://)?(www.)?([^/]*)#i',$redirectUrlUnsub,$resultsurl);
	$domainredirectunsub = preg_replace('#[^a-z0-9\.]#i','',@$resultsurl[3]);
	$saveRedir = false;
	if(!empty($domainredirect) && !preg_match('#^'.$regex.'$#i',$domainredirect)){
		$regex .= '|'.$domainredirect;
		$saveRedir = true;
	}
	if(!empty($domainredirectunsub) && !preg_match('#^'.$regex.'$#i',$domainredirectunsub)){
		$regex .= '|'.$domainredirectunsub;
		$saveRedir = true;
	}
	if($saveRedir){
		$newConfig = new stdClass();
		$newConfig->module_redirect = $regex;
		$config->save($newConfig);
	}
}

$formName = acymailing_getModuleFormName();

$introText = $params->get('introtext');
$postText = $params->get('finaltext');
$mootoolsIntro = $params->get('mootoolsintro','');
if(!empty($introText) && preg_match('#^[A-Z_]*$#',$introText)){
	$introText = JText::_($introText);
}
if(!empty($postText) && preg_match('#^[A-Z_]*$#',$postText)){
	$postText = JText::_($postText);
}
if(!empty($mootoolsIntro) && preg_match('#^[A-Z_]*$#',$mootoolsIntro)){
	$mootoolsIntro = JText::_($mootoolsIntro);
}


if($params->get('effect') == 'mootools-box' AND JRequest::getString('tmpl') != 'component'){

	$mootoolsButton = $params->get('mootoolsbutton','');
	if(empty($mootoolsButton)){
		$mootoolsButton = JText::_('SUBSCRIBE');
	}else{
		if(!empty($mootoolsButton) && preg_match('#^[A-Z_]*$#',$mootoolsButton)){
			$mootoolsButton = JText::_($mootoolsButton);
		}
	}

	$moduleCSS = $config->get('css_module','default');
	if(!empty($moduleCSS)){
		$doc->addStyleSheet( ACYMAILING_CSS.'module_'.$moduleCSS.'.css' );
	}
	JHTML::_('behavior.modal','a.modal');
	require(JModuleHelper::getLayoutPath('mod_acymailing','popup'));
	return;
}
acymailing_initModule($params->get('includejs','header'),$params);

$userClass = acymailing_get('class.subscriber');
$identifiedUser = null;
if($params->get('loggedin',1)){
	$connectedUser = JFactory::getUser();
	if(!empty($connectedUser->email)){
		$identifiedUser = $userClass->get($connectedUser->email);
	}
}

$visibleLists = trim($params->get('lists','None'));
$hiddenLists = trim($params->get('hiddenlists','All'));
$visibleListsArray = array();
$hiddenListsArray = array();
$listsClass = acymailing_get('class.list');
if(empty($identifiedUser->subid)){
	$allLists = $listsClass->getLists('listid');
}else{
	$allLists = $userClass->getSubscription($identifiedUser->subid,'listid');
}


if(strpos($visibleLists,',') OR is_numeric($visibleLists)){
	$allvisiblelists = explode(',',$visibleLists);
	foreach($allLists as $oneList){
		if($oneList->published AND in_array($oneList->listid,$allvisiblelists)) $visibleListsArray[] = $oneList->listid;
	}
}elseif(strtolower($visibleLists) == 'all'){
	foreach($allLists as $oneList){
		if($oneList->published){$visibleListsArray[] = $oneList->listid;}
	}
}

if(strpos($hiddenLists,',') OR is_numeric($hiddenLists)){
	$allhiddenlists = explode(',',$hiddenLists);
	foreach($allLists as $oneList){
		if($oneList->published AND in_array($oneList->listid,$allhiddenlists)) $hiddenListsArray[] = $oneList->listid;
	}
}elseif(strtolower($hiddenLists) == 'all'){
	$visibleListsArray = array();
	foreach($allLists as $oneList){
		if(!empty($oneList->published)){$hiddenListsArray[] = $oneList->listid;}
	}
}

if(!empty($visibleListsArray) AND !empty($hiddenListsArray)){
	$visibleListsArray =  array_diff($visibleListsArray, $hiddenListsArray);
}

$visibleLists = $params->get('dropdown',0) ? '' : implode(',',$visibleListsArray);
$hiddenLists = implode(',',$hiddenListsArray);

if(!$params->get('dropdown',0) && empty($hiddenLists) && empty($visibleLists)){
	echo '<p style="color:red">Error : Please select some lists in your AcyMailing module configuration for the field "'.JText::_('AUTO_SUBSCRIBE_TO').'" and make sure the selected lists are enabled </p>';
}

if(!empty($identifiedUser->subid)){
	$countSub = 0;
	$countUnsub = 0;
	foreach($visibleListsArray as $idOneList){
		if($allLists[$idOneList]->status == -1) $countSub++;
		elseif($allLists[$idOneList]->status == 1) $countUnsub++;
	}
	foreach($hiddenListsArray as $idOneList){
		if($allLists[$idOneList]->status == -1) $countSub++;
		elseif($allLists[$idOneList]->status == 1) $countUnsub++;
	}
}

$checkedLists = $params->get('listschecked','All');
if(strtolower($checkedLists) == 'all'){ $checkedListsArray = $visibleListsArray;}
elseif(strpos($checkedLists,',') OR is_numeric($checkedLists)){ $checkedListsArray = explode(',',$checkedLists);}
else{ $checkedListsArray = array();}

$nameCaption = $params->get('nametext',JText::_('NAMECAPTION'));
$emailCaption = $params->get('emailtext',JText::_('EMAILCAPTION'));
$displayOutside = $params->get('displayfields',0);
$displayInline = ($params->get('displaymode','vertical') == 'vertical') ? false : true;

$displayedFields = $params->get('customfields','name,email');
$fieldsToDisplay = explode(',',$displayedFields);
$extraFields = array();

$fieldsize = $params->get('fieldsize');
if(is_numeric($fieldsize)) $fieldsize .= 'px';

if(!in_array('email',$fieldsToDisplay)) $fieldsToDisplay[] = 'email';

if($params->get('effect') == 'mootools-slide' || $params->get('redirectmode',0) == '3'){
	acymailing_loadMootools();
}

if($params->get('effect') == 'mootools-slide'){
	$mootoolsButton = $params->get('mootoolsbutton','');
	if(empty($mootoolsButton)) $mootoolsButton = JText::_('SUBSCRIBE');

	$js = "window.addEvent('domready', function(){
				var mySlide = new Fx.Slide('acymailing_fulldiv_$formName');
				mySlide.hide();
				try{
					var acytogglemodule = document.id('acymailing_togglemodule_$formName');
				}catch(err){
					var acytogglemodule = $('acymailing_togglemodule_$formName');
				}

				acytogglemodule.addEvent('click', function(e){
					if(mySlide.wrapper.offsetHeight == 0){
						acytogglemodule.className = 'acymailing_togglemodule acyactive';
					}else{
						acytogglemodule.className = 'acymailing_togglemodule';
					}
					mySlide.toggle();
					try {
						var evt = new Event(e);
						evt.stop();
					} catch(err) {
						e.stop();
					}
				});
			});";
	if($params->get('includejs','header') == 'header'){
			$doc->addScriptDeclaration( $js );
	}else{
		echo "<script type=\"text/javascript\">
			<!--
				$js
			//-->
				</script>";
	}
}

if($params->get('overlay',0)){
	JHTML::_('behavior.tooltip');
}

if($params->get('showterms',false)){
	require_once JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php';
	$termsIdContent = $params->get('termscontent',0);
	if(empty($termsIdContent)){
		$termslink = JText::_('JOOMEXT_TERMS');
	}else{
		if(is_numeric($termsIdContent)){
			$db = JFactory::getDBO();
			if(!ACYMAILING_J16){
				$query = 'SELECT a.id,a.alias,a.catid,a.sectionid, c.alias as catalias, s.alias as secalias FROM #__content as a ';
				$query .= ' LEFT JOIN #__categories AS c ON c.id = a.catid ';
				$query .= ' LEFT JOIN #__sections AS s ON s.id = a.sectionid ';
				$query .= 'WHERE a.id = '.$termsIdContent.' LIMIT 1';
				$db->setQuery($query);
				$article = $db->loadObject();

				$section = $article->sectionid. (!empty($article->secalias) ? ':'.$article->secalias : '');
				$category = $article->catid. (!empty($article->catalias) ? ':'.$article->catalias : '');
				$articleid = $article->id. (!empty($article->alias) ? ':'.$article->alias : '');
				$url = ContentHelperRoute::getArticleRoute($articleid,$category,$section);
			}else{
				$query = 'SELECT a.id,a.alias,a.catid, c.alias as catalias FROM #__content as a ';
				$query .= ' LEFT JOIN #__categories AS c ON c.id = a.catid ';
				$query .= 'WHERE a.id = '.$termsIdContent.' LIMIT 1';
				$db->setQuery($query);
				$article = $db->loadObject();

				$category = $article->catid. (!empty($article->catalias) ? ':'.$article->catalias : '');
				$articleid = $article->id. (!empty($article->alias) ? ':'.$article->alias : '');

				$url = ContentHelperRoute::getArticleRoute($articleid,$category);
			}
			$url .= (strpos($url,'?') ? '&':'?').'tmpl=component';
		}else{
			$url = $termsIdContent;
		}

		if($params->get('showtermspopup',1) == 1){
			JHTML::_('behavior.modal','a.modal');
			$termslink = '<a class="modal" title="'.JText::_('JOOMEXT_TERMS',true).'"  href="'.$url.'" rel="{handler: \'iframe\', size: {x: 650, y: 375}}">'.JText::_('JOOMEXT_TERMS').'</a>';
		}else{
			$termslink = '<a title="'.JText::_('JOOMEXT_TERMS',true).'"  href="'.$url.'" target="_blank">'.JText::_('JOOMEXT_TERMS').'</a>';
		}
	}
}

if($params->get('displaymode') == 'tableless'){
	require(JModuleHelper::getLayoutPath('mod_acymailing','tableless'));
}else{
	require(JModuleHelper::getLayoutPath('mod_acymailing'));
}

