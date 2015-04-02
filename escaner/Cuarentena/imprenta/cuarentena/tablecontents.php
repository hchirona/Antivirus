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
defined('_JEXEC') or die('Restricted access');

class plgAcymailingTablecontents extends JPlugin
{

	var $noResult = array();

	function plgAcymailingTablecontents(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('acymailing', 'tablecontents');
			$this->params = new JParameter( $plugin->params );
		}
	}

	 function acymailing_getPluginType() {
		 $onePlugin = new stdClass();
		 $onePlugin->name = JText::_('ACY_TABLECONTENTS');
		 $onePlugin->function = 'acymailingtablecontents_show';
		 $onePlugin->help = 'plugin-tablecontents';

		 return $onePlugin;
	}

	function acymailingtablecontents_show(){

		$contenttype = array();
		$contenttype[] = JHTML::_('select.option', '',JText::_('ACY_EXISTINGANCHOR'));
		for($i = 1;$i<6;$i++){
			$contenttype[] = JHTML::_('select.option', "|type:h".$i,'H'.$i);
		}
		$contenttype[] = JHTML::_('select.option', 'class',JText::_('CLASS_NAME'));

		$contentsubtype = array();
		$contentsubtype[] = JHTML::_('select.option', '',JText::_('ACY_NONE'));
		for($i = 1;$i<6;$i++){
			$contentsubtype[] = JHTML::_('select.option', "|subtype:h".$i,'H'.$i);
		}
		$contentsubtype[] = JHTML::_('select.option', 'class',JText::_('CLASS_NAME'));

		?>

		<script language="javascript" type="text/javascript">
		<!--
		 function updateTag(){
				var tag = '{tableofcontents';
		if(document.adminForm.contenttype.value){
			if(document.adminForm.contenttype.value == 'class'){
				document.adminForm.classvalue.style.display = '';
				tag += '|class:'+document.adminForm.classvalue.value;
			}else{
				document.adminForm.classvalue.style.display = 'none';
				tag += document.adminForm.contenttype.value;
			}
		}
		if(document.adminForm.contentsubtype.value){
			if(document.adminForm.contentsubtype.value == 'class'){
				document.adminForm.subclassvalue.style.display = '';
				tag += '|subclass:'+document.adminForm.subclassvalue.value;
			}else{
				document.adminForm.subclassvalue.style.display = 'none';
				tag += document.adminForm.contentsubtype.value;
			}
		}
				tag += '}';

				setTag(tag);
			}
		//-->
		</script>
		<table width="100%" class="adminform">
			<tr><td colspan="2"><?php echo JText::_('ACY_GENERATEANCHOR'); ?></td></tr>
			<tr><td><?php echo JText::sprintf('ACY_LEVEL',1)?></td><td><?php echo JHTML::_('select.genericlist', $contenttype, 'contenttype' , 'size="1" onchange="updateTag();"', 'value', 'text'); ?><input type="text" style="display:none" onchange="updateTag();" name="classvalue" /></td></tr>
			<tr><td><?php echo JText::sprintf('ACY_LEVEL',2)?></td><td><?php echo JHTML::_('select.genericlist', $contentsubtype, 'contentsubtype' , 'size="1" onchange="updateTag();"', 'value', 'text'); ?><input type="text" style="display:none" onchange="updateTag();" name="subclassvalue" /></td></tr>
		</table>
<?php
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration("window.addEvent('domready', function(){ updateTag(); });");
}


	function acymailing_replaceusertags(&$email,&$user,$send = true){

		if(isset($this->noResult[intval($email->mailid)])) return;

		$match = '#{tableofcontents(.*)}#Ui';

		$variables = array('body','altbody');

			$found = false;
			foreach($variables as $var){
				if(empty($email->$var)) continue;
				$found = preg_match_all($match,$email->$var,$results[$var]) || $found;
				if(empty($results[$var][0])) unset($results[$var]);
			}

			if(!$found){
				$this->noResult[intval($email->mailid)] = true;
				 return;
			}

			$mailerHelper = acymailing_get('helper.mailer');

			$htmlreplace = array();
			$textreplace = array();
			foreach($results as $var => $allresults){
				foreach($allresults[0] as $i => $oneTag){
					if(isset($htmlreplace[$oneTag])) continue;

					$article = $this->_generateTable($allresults,$i,$email);
					$htmlreplace[$oneTag] = $article;
					$textreplace[$oneTag] = $mailerHelper->textVersion($article);
				}
			}

			$email->body = str_replace(array_keys($htmlreplace),$htmlreplace,$email->body);
			$email->altbody = str_replace(array_keys($textreplace),$textreplace,$email->altbody);
	}

	function _generateTable(&$results,$i,&$email){

			$arguments = explode('|',strip_tags($results[1][$i]));
			$tag = new stdClass();
			$tag->divider = $this->params->get('divider','br');
			$tag->before = '';
			$tag->after = '';
			$tag->subdivider = $this->params->get('divider','br');
			$tag->subbefore = '';
			$tag->subafter = '';
			for($i=1,$a=count($arguments);$i<$a;$i++){
				$args = explode(':',$arguments[$i]);
				if(isset($args[1])){
					$tag->$args[0] = $args[1];
				}else{
					$tag->$args[0] = true;
				}
			}

			if($tag->divider == 'br'){
				$tag->divider = '<br />';
				$tag->subbefore = $tag->subdivider = '<br /> - ';
			}elseif($tag->divider == 'space'){
				$tag->subdivider = ', ';
				$tag->divider = ' ';
				$tag->subbefore = ' ( ';
				$tag->subafter = ' ) ';
			}elseif($tag->divider == 'li'){
				$tag->subdivider = $tag->divider = '</li><li>';
				$tag->subbefore = $tag->before = '<ul><li>';
				$tag->subafter = $tag->after = '</li></ul>';
			}

		$this->updateMail = array();
		$this->links = array();
		$this->sublinks = array();
		$anchorLinks = $this->_findLinks($tag,$email);
		if(!empty($tag->subtype) || !empty($tag->subclass)){
			$anchorSubLinks = $this->_findLinks($tag,$email,true);
			if(empty($this->links)){
				$this->links = $this->sublinks; unset($this->sublinks);
			}
		}

		$links = $this->links;

		if(empty($links)) return '';
		if(!empty($this->updateMail)) $email->body = str_replace(array_keys($this->updateMail),$this->updateMail,$email->body);

		if(!empty($this->sublinks)){
			$sublinks = $this->sublinks;
			foreach($links as $ilink => $oneLink){
				$allsublinks = array();
				$from = $anchorLinks['pos'][$ilink];
				$to =  empty($anchorLinks['pos'][$ilink+1]) ? 9999999999999 : $anchorLinks['pos'][$ilink+1];
				foreach($sublinks as $isublink => $oneSubLink){
					if($anchorSubLinks['pos'][$isublink] > $to) break;
					if($anchorSubLinks['pos'][$isublink] > $from) $allsublinks[] = $oneSubLink;
				}
				if(!empty($allsublinks)) $links[$ilink] = $links[$ilink].$tag->subbefore.implode($tag->subdivider,$allsublinks).$tag->subafter;
			}
		}

		$result = '<div class="tableofcontents">'.$tag->before.implode($tag->divider,$links).$tag->after.'</div>';
		if(file_exists(ACYMAILING_MEDIA.'plugins'.DS.'tablecontents.php')){
			ob_start();
			require(ACYMAILING_MEDIA.'plugins'.DS.'tablecontents.php');
			$result = ob_get_clean();
		}
		return $result;

	}

	function _findLinks(&$tag,&$email,$sub = false){
		if($sub){
			$varType = 'subtype';
			$varClass = 'subclass';
			$varLink = &$this->sublinks;
		}else{
			$varType = 'type';
			$varClass = 'class';
			$varLink = &$this->links;
		}
		if(!empty($tag->$varType)){
			preg_match_all('#<'.$tag->$varType.'[^>]*>((?!</ *'.$tag->$varType.'>).)*</ *'.$tag->$varType.'>#Uis',$email->body,$anchorresults);
		}elseif(!empty($tag->class)){
			preg_match_all('#<[^>]*class="'.$tag->$varClass.'"[^>]*>(<[^>]*>|[^<>])*</.*>#Uis',$email->body,$anchorresults);
			$tag->$varType = 'item';
		}else{
			preg_match_all('#<a[^>]*name="([^">]*)"[^>]*>((?!</ *a>).)*</ *a>#Uis',$email->body,$anchorresults);
		}

		if(empty($anchorresults)) return '';


		foreach($anchorresults[0] as $i => $oneContent){
			$anchorresults['pos'][$i] = strpos($email->body,$oneContent);
			$linktext = strip_tags($oneContent);
			if(empty($linktext)) continue;
			if(empty($tag->$varType)){
				$varLink[$i] = '<a href="#'.$anchorresults[1][$i].'" class="oneitem" >'.$linktext.'</a>';
			}else{
				$varLink[$i] = '<a href="#'.$tag->$varType.$i.'" class="oneitem oneitem'.$tag->$varType.'" >'.$linktext.'</a>';
				$this->updateMail[$oneContent] = $oneContent.'<a name="'.$tag->$varType.$i.'"></a>';
			}

		}

		return $anchorresults;
	}
}//endclass
