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

class plgAcymailingTaguser extends JPlugin
{

	var $sendervalues =array();

	function plgAcymailingTaguser(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('acymailing', 'taguser');
			$this->params = new JParameter( $plugin->params );
		}
	}

	 function acymailing_getPluginType() {

	 	$onePlugin = new stdClass();
	 	$onePlugin->name = JText::_('TAGUSER_TAGUSER');
	 	$onePlugin->function = 'acymailingtaguser_show';
	 	$onePlugin->help = 'plugin-taguser';

	 	return $onePlugin;
	 }

	 function acymailingtaguser_show(){?>

		<script language="javascript" type="text/javascript">
			function applyTag(tagname){
				var string = '{usertag:'+tagname;
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


		$notallowed = array('password','params','sendemail','gid','block','email','name','id');
		$text = '<table class="adminlist table table-striped table-hover" cellpadding="1">';
		$db = JFactory::getDBO();
		$fields = acymailing_getColumns('#__users');

	 	$descriptions['username'] = JText::_('TAGUSER_USERNAME');
	 	$descriptions['usertype'] = JText::_('TAGUSER_GROUP');
	 	$descriptions['lastvisitdate'] = JText::_('TAGUSER_LASTVISIT');
	 	$descriptions['registerdate'] = JText::_('TAGUSER_REGISTRATION');

		$k = 0;
		foreach($fields as $fieldname => $oneField){
			if(in_array(strtolower($fieldname),$notallowed)) continue;
			$type = '';
			if(strpos(strtolower($oneField),'date') !== false) $type = '|type:date';
			$text .= '<tr style="cursor:pointer" class="row'.$k.'" onclick="applyTag(\''.$fieldname.$type.'\');" ><td class="acytdcheckbox"></td><td>'.$fieldname.'</td><td>'.@$descriptions[strtolower($fieldname)].'</td></tr>';
			$k = 1-$k;
		}

		if(ACYMAILING_J16){
			$db->setQuery('SELECT DISTINCT `profile_key` FROM `#__user_profiles`');
			$extraFields = $db->loadObjectList();
			if(!empty($extraFields)){
				foreach($extraFields as $oneField){
					$text .= '<tr style="cursor:pointer" class="row'.$k.'" onclick="applyTag(\''.$oneField->profile_key.'|type:extra\');" ><td class="acytdcheckbox"></td><td>'.$oneField->profile_key.'</td><td></td></tr>';
					$k = 1-$k;
				}
			}
		}

		$text .= '</table>';

		echo $text;
	 }

	function acymailing_replaceusertags(&$email,&$user,$send = true){

		$match = '#(?:{|%7B)usertag:(.*)(?:}|%7D)#Ui';
		$variables = array('subject','body','altbody');
		$found = false;
		foreach($variables as $var){
			if(empty($email->$var)) continue;
			$found = preg_match_all($match,$email->$var,$results[$var]) || $found;
			if(empty($results[$var][0])) unset($results[$var]);
		}

		if(!$found) return;

		$db = JFactory::getDBO();
		$pluginsHelper = acymailing_get('helper.acyplugins');
		$receivervalues = new stdClass();

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
						$db->setQuery('SELECT * FROM '.acymailing_table('users',false).' WHERE id = '.$email->userid.' LIMIT 1');
						$this->sendervalues[$email->mailid] = $db->loadObject();

						if(ACYMAILING_J16){
							$db->setQuery('SELECT * FROM #__user_profiles WHERE user_id = '.$email->userid);
							$receivervalues->extraFields = $db->loadObjectList('profile_key');
						}
					}
					if(!empty($this->sendervalues[$email->mailid])) $values = $this->sendervalues[$email->mailid];
				}else{
					if(empty($receivervalues->id) AND !empty($user->userid)){
						$db->setQuery('SELECT * FROM '.acymailing_table('users',false).' WHERE id = '.$user->userid.' LIMIT 1');
						$receivervalues = $db->loadObject();

						if(ACYMAILING_J16){
							$db->setQuery('SELECT * FROM #__user_profiles WHERE user_id = '.$user->userid);
							$receivervalues->extraFields = $db->loadObjectList('profile_key');
						}
					}
					if(!empty($receivervalues->id)) $values = $receivervalues;
				}

				if(empty($mytag->type) || $mytag->type != 'extra'){
					$replaceme = isset($values->$field) ? $values->$field : $mytag->default;
				}else{
					$replaceme = isset($values->extraFields[$field]) ? trim($values->extraFields[$field]->profile_value,'"') : $mytag->default;
				}

				$tags[$oneTag] = $replaceme;
				$pluginsHelper->formatString($tags[$oneTag],$mytag);
			}
		}

		foreach($results as $var => $allresults){
			$email->$var = str_replace(array_keys($tags),$tags,$email->$var);
		}
	 }//endfct

 	function onAcyDisplayFilters(&$type,$context="massactions"){

		if($this->params->get('displayfilter_'.$context,true) == false) return;

		$db = JFactory::getDBO();
		$fields = acymailing_getColumns('#__users');
		if(empty($fields)) return;

		$type['joomlafield'] = JText::_('JOOMLA_FIELD');
		$type['joomlagroup'] = JText::_('ACY_GROUP');

		$field = array();
		foreach($fields as $oneField => $fieldType){
			$field[] = JHTML::_('select.option',$oneField,$oneField);
		}

		if(ACYMAILING_J16){
			$db->setQuery('SELECT DISTINCT `profile_key` FROM `#__user_profiles`');
			$extraFields = $db->loadObjectList();
			if(!empty($extraFields)){
				foreach($extraFields as $oneField){
					$field[] = JHTML::_('select.option','customfield_'.$oneField->profile_key,$oneField->profile_key);
				}
			}
		}

		$operators = acymailing_get('type.operators');
		$operators->extra = 'onchange="countresults(__num__)"';

		$return = '<div id="filter__num__joomlafield">'.JHTML::_('select.genericlist',   $field, "filter[__num__][joomlafield][map]", 'class="inputbox" size="1" onchange="countresults(__num__)"', 'value', 'text');
		$return.= ' '.$operators->display("filter[__num__][joomlafield][operator]").' <input onchange="countresults(__num__)" class="inputbox" type="text" name="filter[__num__][joomlafield][value]" style="width:200px" value=""></div>';

		if(!ACYMAILING_J16){
			$acl = JFactory::getACL();
			$groups = $acl->get_group_children_tree( null, 'USERS', false );
		}else{
			$db = JFactory::getDBO();
			$db->setQuery('SELECT a.*, a.title as text, a.id as value  FROM #__usergroups AS a ORDER BY a.lft ASC');
			$groups = $db->loadObjectList('id');
			foreach($groups as $id => $group){
				if(isset($groups[$group->parent_id])){
					$groups[$id]->level = intval(@$groups[$group->parent_id]->level) + 1;
					$groups[$id]->text = str_repeat('- - ',$groups[$id]->level).$groups[$id]->text;
				}
			}
		}

		$inoperator = acymailing_get('type.operatorsin');
		$inoperator->js = 'onchange="countresults(__num__)"';

		$return .= '<div id="filter__num__joomlagroup">'.$inoperator->display("filter[__num__][joomlagroup][type]").' '.JHTML::_('select.genericlist',   $groups, "filter[__num__][joomlagroup][group]", 'class="inputbox" size="1" onchange="countresults(__num__)"', 'value', 'text').'</div>';

	 	return $return;
	 }

	function onAcyProcessFilterCount_joomlafield(&$query,$filter,$num){
	 	$this->onAcyProcessFilter_joomlafield($query,$filter,$num);
		return JText::sprintf('SELECTED_USERS',$query->count());
	 }

	 function onAcyDisplayFilter_joomlafield($filter){
		return JText::_('JOOMLA_FIELD').' : '.$filter['map'].' '.$filter['operator'].' '.$filter['value'];
	 }


	function onAcyProcessFilter_joomlafield(&$query,$filter,$num){
		if(strpos($filter['map'],'customfield_') !== false){
			$query->leftjoin['joomlauserprofiles'.$num] = '#__user_profiles AS joomlauserprofiles'.$num.' ON joomlauserprofiles'.$num.'.user_id = sub.userid AND joomlauserprofiles'.$num.'.profile_key = '.$query->db->Quote(str_replace('customfield_','',$filter['map']));
			$val = trim($filter['value'],'"');
			if(in_array($filter['operator'],array('=','!=','<','>','<=','>=','BEGINS','LIKE','NOT LIKE'))){
				$val = '"'.$val;
			}
			if(in_array($filter['operator'],array('=','!=','<','>','<=','>=','END','LIKE','NOT LIKE'))){
				$val = $val.'"';
			}

			$query->where[] = $query->convertQuery('joomlauserprofiles'.$num,'profile_value',$filter['operator'],$val);
		}else{
			$query->leftjoin['joomlauser'] = '#__users AS joomlauser ON joomlauser.id = sub.userid';
		 	if(in_array($filter['map'],array('registerDate','lastvisitDate'))){
		 		$filter['value'] = acymailing_replaceDate($filter['value']);
		 		if(!is_numeric($filter['value'])) $filter['value'] = strtotime($filter['value']);
				$filter['value'] = strftime('%Y-%m-%d %H:%M:%S',$filter['value']);
			}
		 	$query->where[] = $query->convertQuery('joomlauser',$filter['map'],$filter['operator'],$filter['value']);
		}
	 }

	function onAcyProcessFilterCount_joomlagroup(&$query,$filter,$num){
		$this->onAcyProcessFilter_joomlagroup($query,$filter,$num);
		return JText::sprintf('SELECTED_USERS',$query->count());
	}

	function onAcyProcessFilter_joomlagroup(&$query,$filter,$num){
		$operator = (empty($filter['type']) || $filter['type'] == 'IN') ? 'IS NOT NULL' : "IS NULL";
		if(!ACYMAILING_J16){
			$query->leftjoin['joomlauser'.$num] = "#__users AS joomlauser$num ON joomlauser$num.id = sub.userid AND joomlauser$num.gid = ".intval($filter['group']);
	 		$query->where[] = "joomlauser$num.id ".$operator;
		}else{
			$query->leftjoin['joomlauser'.$num] = "#__user_usergroup_map AS joomlauser$num ON joomlauser$num.user_id = sub.userid AND joomlauser$num.group_id = ".intval($filter['group']);
	 		$query->where[] = "joomlauser$num.user_id ".$operator;
		}

	 }
}//endclass
