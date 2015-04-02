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

class plgAcymailingTagsubscriber extends JPlugin
{

	function plgAcymailingTagsubscriber(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('acymailing', 'tagsubscriber');
			$this->params = new JParameter( $plugin->params );
		}
	}

	 function acymailing_getPluginType() {
	 	$onePlugin = new stdClass();
	 	$onePlugin->name = JText::_('SUBSCRIBER_SUBSCRIBER');
	 	$onePlugin->function = 'acymailingtagsubscriber_show';
	 	$onePlugin->help = 'plugin-tagsubscriber';

	 	return $onePlugin;
	 }

	 function acymailingtagsubscriber_show(){

	 	$descriptions['subid'] = JText::_('SUBSCRIBER_ID');
	 	$descriptions['email'] = JText::_('SUBSCRIBER_EMAIL');
	 	$descriptions['name'] = JText::_('SUBSCRIBER_NAME');
	 	$descriptions['userid'] = JText::_('SUBSCRIBER_USERID');
	 	$descriptions['ip'] = JText::_('SUBSCRIBER_IP');
	 	$descriptions['created'] = JText::_('SUBSCRIBER_CREATED');

		$text = '<table class="adminlist table table-striped table-hover" cellpadding="1">';
		$db = JFactory::getDBO();
		$fields = acymailing_getColumns('#__acymailing_subscriber');

		$others = array();

		$others['{subtag:name|part:first|ucfirst}'] = array('name'=> JText::_('SUBSCRIBER_FIRSTPART'), 'desc'=>JText::_('SUBSCRIBER_FIRSTPART').' '.JText::_('SUBSCRIBER_FIRSTPART_DESC'));
		$others['{subtag:name|part:last|ucfirst}'] = array('name'=> JText::_('SUBSCRIBER_LASTPART'), 'desc'=>JText::_('SUBSCRIBER_LASTPART').' '.JText::_('SUBSCRIBER_LASTPART_DESC'));

		$k = 0;

		foreach($others as $tagname => $tag){
			$text .= '<tr style="cursor:pointer" class="row'.$k.'" onclick="setTag(\''.$tagname.'\');insertTag();" ><td class="acytdcheckbox"></td><td>'.$tag['name'].'</td><td>'.$tag['desc'].'</td></tr>';
			$k = 1-$k;
		}
		foreach($fields as $fieldname => $oneField){
			if(!isset($descriptions[$fieldname]) AND $oneField == 'tinyint') continue;

			$type = '';
			if(in_array($fieldname,array('created','confirmed_date','lastclick_date','lastopen_date'))) $type = '|type:time';
			$text .= '<tr style="cursor:pointer" class="row'.$k.'" onclick="setTag(\'{subtag:'.$fieldname.$type.'}\');insertTag();" ><td class="acytdcheckbox"></td><td>'.$fieldname.'</td><td>'.@$descriptions[$fieldname].'</td></tr>';
			$k = 1-$k;
		}

		$text .= '</table>';

		echo $text;
	 }

	function acymailing_replaceusertags(&$email,&$user,$send = true){
		$match = '#(?:{|%7B)subtag:(.*)(?:}|%7D)#Ui';
		$variables = array('subject','body','altbody');
		$found = false;
		foreach($variables as $var){
			if(empty($email->$var)) continue;
			$found = preg_match_all($match,$email->$var,$results[$var]) || $found;
			if(empty($results[$var][0])) unset($results[$var]);
		}

		$otherVars = array();
		if($send){
			if(!empty($email->From)) $otherVars['from'] =& $email->From;
			if(!empty($email->FromName)) $otherVars['fromname'] =& $email->FromName;
			if(!empty($email->ReplyTo)){
				foreach($email->ReplyTo as $i => $replyto){
					foreach($replyto as $a => $oneval){
						$otherVars['replyto'.$i.$a] =& $email->ReplyTo[$i][$a];
					}
				}
			}

			if(!empty($otherVars)){
				foreach($otherVars as $var => $val){
					$found = preg_match_all($match,$val,$results[$var]) || $found;
					if(empty($results[$var][0])) unset($results[$var]);
				}
			}
		}

		if(!$found) return;

		$this->pluginsHelper = acymailing_get('helper.acyplugins');

		$tags = array();
		foreach($results as $var => $allresults){
			foreach($allresults[0] as $i => $oneTag){
				if(isset($tags[$oneTag])) continue;

				$tags[$oneTag] = $this->replaceSubTag($allresults,$i,$user);
			}
		}

		foreach($variables as $var){
			$email->$var = str_replace(array_keys($tags),$tags,$email->$var);
		}

		if(!empty($otherVars)){
			foreach($otherVars as $var => $val){
				$otherVars[$var] = str_replace(array_keys($tags),$tags,$otherVars[$var]);
			}
		}
	}

	function replaceSubTag(&$allresults,$i,&$user){

		$arguments = explode('|',strip_tags($allresults[1][$i]));
		$field = $arguments[0];
		unset($arguments[0]);
		$mytag = new stdClass();
		$mytag->default = '';
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
		$replaceme = (isset($user->$field) && strlen($user->$field) > 0) ? $user->$field : $mytag->default;
		$replaceme = nl2br($replaceme);

		$this->pluginsHelper->formatString($replaceme,$mytag);

		return $replaceme;
	}

	function onAcyDisplayFilters(&$type,$context="massactions"){

		if($this->params->get('displayfilter_'.$context,true) == false) return;

		$db = JFactory::getDBO();
		$fields = acymailing_getColumns('#__acymailing_subscriber');
		if(empty($fields)) return;

		$field = array();
		foreach($fields as $oneField => $fieldType){
			$field[] = JHTML::_('select.option',$oneField,$oneField);
		}
		$type['acymailingfield'] = JText::_('ACYMAILING_FIELD');

		$operators = acymailing_get('type.operators');
		$operators->extra = 'onchange="countresults(__num__)"';

		$return = '<div id="filter__num__acymailingfield">'.JHTML::_('select.genericlist',   $field, "filter[__num__][acymailingfield][map]", 'onchange="countresults(__num__)" class="inputbox" size="1"', 'value', 'text');
		$return.= ' '.$operators->display("filter[__num__][acymailingfield][operator]").' <input onchange="countresults(__num__)" class="inputbox" type="text" name="filter[__num__][acymailingfield][value]" style="width:200px" value=""></div>';

	 	return $return;
	 }

	 function onAcyDisplayFilter_acymailingfield($filter){
		return JText::_('ACYMAILING_FIELD').' : '.$filter['map'].' '.$filter['operator'].' '.$filter['value'];
	 }

	function onAcyProcessFilter_acymailingfield(&$query,$filter,$num){
		$value = acymailing_replaceDate($filter['value']);

		if(strpos($filter['value'],'{time}') !== false && !in_array($filter['map'],array('created','confirmed_date','lastclick_date','lastopen_date'))){
			$value = strftime('%Y-%m-%d',$value);
		}

		if(!is_numeric($value) && (in_array($filter['map'],array('created','confirmed_date','lastclick_date','lastopen_date')))) $value = strtotime($value);
	 	$query->where[] = $query->convertQuery('sub',$filter['map'],$filter['operator'],$value);
	}

	function onAcyProcessFilterCount_acymailingfield(&$query,$filter,$num){
		$this->onAcyProcessFilter_acymailingfield($query,$filter,$num);
		return JText::sprintf('SELECTED_USERS',$query->count());
	}

	function onAcyDisplayActions(&$type){
	 	$type['acymailingfield'] = JText::_('BOUNCE_ACTION');
	 	$status = array();
		$status[] = JHTML::_('select.option','confirm',JText::_('CONFIRM_USERS'));
		$status[] = JHTML::_('select.option','enable',JText::_('ENABLE_USERS'));
		$status[] = JHTML::_('select.option','block',JText::_('BLOCK_USERS'));
		$status[] = JHTML::_('select.option','delete',JText::_('DELETE_USERS'));

		$content = '';

		if(acymailing_level(3)){

			$db = JFactory::getDBO();
			$fields = acymailing_getColumns('#__acymailing_subscriber');
			if(empty($fields)) return;

			$field = array();
			foreach($fields as $oneField => $fieldType){
				if(in_array($oneField,array('name','email','confirmed','subid','created','ip'))) continue;
				$field[] = JHTML::_('select.option',$oneField,$oneField);
			}

			$content .= '<div id="action__num__acymailingfieldval">'.JHTML::_('select.genericlist',   $field, "action[__num__][acymailingfieldval][map]", 'class="inputbox" size="1"', 'value', 'text');
			$content .= ' = <input class="inputbox" type="text" name="action[__num__][acymailingfieldval][value]" style="width:200px" value=""></div>';

			$type['acymailingfieldval'] = JText::_('SET_SUBSCRIBER_VALUE');
		}

		$content .= '<div id="action__num__acymailingfield">'.JHTML::_('select.genericlist',   $status, "action[__num__][acymailingfield][action]", 'class="inputbox" size="1"', 'value', 'text').'</div>';

	 	return $content;
	 }

	function onAcyProcessAction_acymailingfieldval($cquery,$action,$num){
		$query = 'UPDATE #__acymailing_subscriber as sub';
		if(!empty($cquery->join)) $query .= ' JOIN '.implode(' JOIN ',$cquery->join);
		if(!empty($cquery->leftjoin)) $query .= ' LEFT JOIN '.implode(' LEFT JOIN ',$cquery->leftjoin);
		$query .= " SET sub.`".$action['map']."` = ".$cquery->db->Quote($action['value']);
		if(!empty($cquery->where)) $query .= ' WHERE ('.implode(') AND (',$cquery->where).')';

		$cquery->db->setQuery($query);
		$cquery->db->query();
		$nbAffected = $cquery->db->getAffectedRows();
		return JText::sprintf('NB_MODIFIED',$nbAffected);
	}

	 function onAcyProcessAction_acymailingfield($cquery,$action,$num){

		$subClass = acymailing_get('class.subscriber');

	 	if($action['action'] == 'confirm'){
	 		$cquery->where['confirmed'] = 'sub.confirmed = 0';
			$cquery->db->setQuery($cquery->getQuery(array('sub.subid')));
			$allSubids = acymailing_loadResultArray($cquery->db);
			if(!empty($allSubids)){
				$subClass->sendConf = false;
				$subClass->sendWelcome = false;
				$subClass->sendNotif = false;
				foreach($allSubids as $oneId){
					$subClass->confirmSubscription($oneId);
				}
			}
			unset($cquery->where['confirmed']);
			return JText::sprintf('NB_CONFIRMED',count($allSubids));
	 	}

	 	if($action['action'] == 'enable'){
			$query = 'UPDATE #__acymailing_subscriber as sub';
			if(!empty($cquery->join)) $query .= ' JOIN '.implode(' JOIN ',$cquery->join);
			if(!empty($cquery->leftjoin)) $query .= ' LEFT JOIN '.implode(' LEFT JOIN ',$cquery->leftjoin);
			$query .= " SET sub.enabled = 1";
			if(!empty($cquery->where)) $query .= ' WHERE ('.implode(') AND (',$cquery->where).')';

			$cquery->db->setQuery($query);
			$cquery->db->query();
			$nbAffected = $cquery->db->getAffectedRows();
			return JText::sprintf('NB_ENABLED',$nbAffected);
	 	}

	 	if($action['action'] == 'block'){
	 		$query = 'UPDATE #__acymailing_subscriber as sub';
	 		if(!empty($cquery->join)) $query .= ' JOIN '.implode(' JOIN ',$cquery->join);
			if(!empty($cquery->leftjoin)) $query .= ' LEFT JOIN '.implode(' LEFT JOIN ',$cquery->leftjoin);
			$query .= " SET sub.enabled = 0";
			if(!empty($cquery->where)) $query .= ' WHERE ('.implode(') AND (',$cquery->where).')';

			$cquery->db->setQuery($query);
			$cquery->db->query();
			$nbAffected = $cquery->db->getAffectedRows();
			return JText::sprintf('NB_BLOCKED',$nbAffected);
	 	}

	 	if($action['action'] == 'delete'){
 			$query = $cquery->getQuery(array('sub.subid'));
			$cquery->db->setQuery($query);
			$allSubids = acymailing_loadResultArray($cquery->db);
			$nbAffected = $subClass->delete($allSubids);
			return JText::sprintf('IMPORT_DELETE',$nbAffected);
	 	}

	 	return 'Filter AcyMailingField error, action not found : '.$action['action'];
	}

}//endclass
