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

class plgAcymailingTagcbuser extends JPlugin
{
	var $sendervalues =array();

	function plgAcymailingTagcbuser(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('acymailing', 'tagcbuser');
			$this->params = new JParameter( $plugin->params );
		}
	}

	 function acymailing_getPluginType() {

	 	$onePlugin = new stdClass();
	 	$onePlugin->name = JText::_('CB User');
	 	$onePlugin->function = 'acymailingtagcb_show';
	 	$onePlugin->help = 'plugin-tagcbuser';

	 	return $onePlugin;
	 }

	function onAcyDisplayFilters(&$type,$context="massactions"){

		if($this->params->get('displayfilter_'.$context,true) == false) return;
		if(!file_exists(ACYMAILING_ROOT .'components'.DS.'com_comprofiler'.DS.'comprofiler.php')) return;

		$db = JFactory::getDBO();
		$fields = acymailing_getColumns('#__comprofiler');
		if(empty($fields)) return;

		$cbfield = array();
		foreach($fields as $oneField => $fieldType){
			$cbfield[] = JHTML::_('select.option',$oneField,$oneField);
		}
		$type['cbfield'] = JText::_('CB_FIELD');

		$operators = acymailing_get('type.operators');

		$return = '<div id="filter__num__cbfield">'.JHTML::_('select.genericlist',   $cbfield, "filter[__num__][cbfield][map]", 'class="inputbox" size="1"', 'value', 'text');
		$return.= ' '.$operators->display("filter[__num__][cbfield][operator]").' <input class="inputbox" type="text" name="filter[__num__][cbfield][value]" style="width:200px" value="" /></div>';

	 	return $return;
	 }

	function onAcyProcessFilter_cbfield(&$query,$filter,$num){
	 	$query->leftjoin['cbfield'] = '#__comprofiler AS cbfield ON cbfield.id = sub.userid';
	 	$query->where[] = $query->convertQuery('cbfield',$filter['map'],$filter['operator'],$filter['value']);
	 }

	 function onAcyProcessFilterCount_cbfield(&$query,$filter,$num){
	 	$this->onAcyProcessFilter_cbfield($query,$filter,$num);
		return JText::sprintf('SELECTED_USERS',$query->count());
	 }

	 function acymailingtagcb_show(){?>

		<script language="javascript" type="text/javascript">
			function applyTag(tagname){
				var string = '{cbtag:'+tagname;
				for(var i=0; i < document.adminForm.typeinfo.length; i++){
					 if (document.adminForm.typeinfo[i].checked){ string += '|info:'+document.adminForm.typeinfo[i].value; }
				}
				string += '}';
				setTag(string);
				insertTag();
			}
		</script>
	<?php
		$typeinfo = array();
		$typeinfo[] = JHTML::_('select.option', "receiver",JText::_('RECEIVER_INFORMATION'));
		$typeinfo[] = JHTML::_('select.option', "sender",JText::_('SENDER_INFORMATIONS'));
		echo JHTML::_('acyselect.radiolist', $typeinfo, 'typeinfo' , '', 'value', 'text', 'receiver');

		$text = '<table class="adminlist table table-striped table-hover" cellpadding="1">';
		$db = JFactory::getDBO();
		$fields = acymailing_getColumns('#__comprofiler');

		$db->setQuery('SELECT name,type FROM #__comprofiler_fields');
		$fieldType = $db->loadObjectList('name');

		$k = 0;

		$text .= '<tr style="cursor:pointer" class="row1" onclick="applyTag(\'thumb\');" ><td class="acytdcheckbox"></td><td>Thumb Avatar</td></tr>';
		foreach($fields as $fieldname => $oneField){
			$type = '';
			if(strpos(strtolower($oneField),'date') !== false) $type = '|type:date';
			if(!empty($fieldType[$fieldname]) AND $fieldType[$fieldname]->type == 'image') $type = '|type:image';
			$text .= '<tr style="cursor:pointer" class="row'.$k.'" onclick="applyTag(\''.$fieldname.$type.'\');" ><td class="acytdcheckbox"></td><td>'.$fieldname.'</td></tr>';
			$k = 1-$k;
		}


		$db->setQuery("SELECT * FROM #__comprofiler_fields WHERE tablecolumns = '' AND published = 1");
		$otherFields = $db->loadObjectList();
		foreach($otherFields as $oneField){
			$text .= '<tr style="cursor:pointer" class="row'.$k.'" onclick="applyTag(\'cbapi_'.$oneField->name.'\');" ><td class="acytdcheckbox"></td><td>'.$oneField->name.'</td></tr>';
			$k = 1-$k;
		}

		$text .= '</table>';

		echo $text;
	 }

	function acymailing_replaceusertags(&$email,&$user,$send = true){

		$match = '#(?:{|%7B)cbtag:(.*)(?:}|%7D)#Ui';
		$variables = array('subject','body','altbody');
		$found = false;
		foreach($variables as $var){
			if(empty($email->$var)) continue;
			$found = preg_match_all($match,$email->$var,$results[$var]) || $found;
			if(empty($results[$var][0])) unset($results[$var]);
		}

		if(!$found) return;

		$uservalues = null;
		$db= JFactory::getDBO();
		if(!empty($user->userid)){
			$db->setQuery('SELECT * FROM '.acymailing_table('comprofiler',false).' WHERE user_id = '.$user->userid.' LIMIT 1');
			$uservalues = $db->loadObject();
		}

		include_once( ACYMAILING_ROOT . 'administrator'.DS.'components'.DS.'com_comprofiler'.DS.'plugin.foundation.php' );
		cbimport( 'cb.database' );
		$pluginsHelper = acymailing_get('helper.acyplugins');
		$currentCBUser = null;

		$tags = array();
		foreach($results as $var => $allresults){
			foreach($allresults[0] as $i => $oneTag){
				if(isset($tags[$oneTag])) continue;

				$arguments = explode('|',$allresults[1][$i]);
				$field = $arguments[0];
				unset($arguments[0]);
				$mytag = new stdClass();
				$mytag->default = $this->params->get('default_'.$field,'');
				if(!empty($arguments)){
					foreach($arguments as $onearg){
						$args = explode(':',$onearg);
						if(isset($args[1])){
							$mytag->$args[0] = $args[1];
						}else{
							$mytag->$args[0] = 1;
						}
					}
				}

				$values = new stdClass();

				if(!empty($mytag->info) AND $mytag->info == 'sender'){
					if(empty($this->sendervalues[$email->mailid]) AND !empty($email->userid)){
						$db->setQuery('SELECT * FROM #__comprofiler WHERE user_id = '.$email->userid.' LIMIT 1');
						$this->sendervalues[$email->mailid] = $db->loadObject();
					}
					if(!empty($this->sendervalues[$email->mailid])) $values = $this->sendervalues[$email->mailid];
				}else{
					$values = $uservalues;
				}

				if(substr($field,0,6) == 'cbapi_'){
					if(!empty($mytag->info) AND $mytag->info == 'sender'){
						if(empty($this->sendervalues[$email->mailid]->$field) AND !empty($email->userid)){
							$currentSender = CBuser::getInstance( $email->userid );
							$values->$field = $currentSender->getField(substr($field,6),$mytag->default,'html', 'none', 'profile', 0, true);
							$this->sendervalues[$email->mailid]->$field = $values->$field;
						}elseif(!empty($this->sendervalues[$email->mailid]->$field)){
							$values->$field = @$this->sendervalues[$email->mailid]->$field;
						}
					}elseif(!empty($user->userid)){
						if(empty($currentCBUser))	$currentCBUser = CBuser::getInstance( $user->userid );
						if(!empty($currentCBUser))	$values->$field = $currentCBUser->getField(substr($field,6),$mytag->default,'html', 'none', 'profile', 0, true);
					}
				}

				$replaceme = isset($values->$field) ? $values->$field : $mytag->default;
				if(!empty($mytag->type)){
					if($mytag->type == 'image' AND !empty($replaceme)){
						$replaceme = '<img src="'.ACYMAILING_LIVE.'images/comprofiler/'.$replaceme.'" alt="'.htmlspecialchars(@$user->name,ENT_COMPAT, 'UTF-8').'" />';
					}
				}

				if($field == 'thumb'){
					$replaceme = '<img src="'.ACYMAILING_LIVE.'images/comprofiler/tn'.$values->avatar.'" alt="'.htmlspecialchars(@$user->name,ENT_COMPAT, 'UTF-8').'" />';
				}elseif($field == 'avatar'){
					$replaceme = '<img src="'.ACYMAILING_LIVE.'images/comprofiler/'.$values->avatar.'" alt="'.htmlspecialchars(@$user->name,ENT_COMPAT, 'UTF-8').'" />';
				}

				$tags[$oneTag] = $replaceme;
				$pluginsHelper->formatString($tags[$oneTag],$mytag);
			}
		}

		foreach($results as $var => $allresults){
			$email->$var = str_replace(array_keys($tags),$tags,$email->$var);
		}
	 }//endfct
}//endclass
