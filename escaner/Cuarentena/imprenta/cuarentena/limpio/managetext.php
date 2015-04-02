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

class plgAcymailingManagetext extends JPlugin
{
	var $foundtags = array();

	function plgAcymailingManagetext(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('acymailing', 'managetext');
			$this->params = new JParameter( $plugin->params );
		}
	}

	function acymailing_replacetags(&$email,$send = true){
		$this->_replaceConstant($email);
	}

	function acymailing_replaceusertags(&$email,&$user,$send = true){
		$this->_removetext($email);
		$this->_addfooter($email);
		$this->_ifstatement($email,$user);
		$this->_addbcc($email);
	}

	function _addbcc(&$email){
		$bcc = trim(str_replace(',',';',$this->params->get('bccaddresses')));
		$mailids = trim(str_replace(',',';',$this->params->get('bccmailids')));
		if(empty($mailids) || empty($bcc) || empty($email->mailid)) return;

		if(!method_exists($email,'AddBCC')) return;

		if(!in_array($email->mailid,explode(';',$mailids))) return;

		$allBcc = explode(';',$bcc);
		foreach($allBcc as $oneBcc){
			$email->AddBCC($oneBcc);
		}
	}

	function _replaceConstant(&$email){
		$match = '#(?:{|%7B)(const|trans|config):(.*)(?:}|%7D)#Uis';
		$variables = array('subject','body','altbody');
		$found = false;
		foreach($variables as $var){
			if(empty($email->$var)) continue;
			$found = preg_match_all($match,$email->$var,$results[$var]) || $found;
			if(empty($results[$var][0])) unset($results[$var]);
		}

		if(!$found) return;

		$jconfig = JFactory::getConfig();

		$tags = array();
		foreach($results as $var => $allresults){
			foreach($allresults[0] as $i => $oneTag){
				$val = trim($allresults[2][$i]);
				if(empty($val)) continue;
				$type = strtolower(trim($allresults[1][$i]));
				if($type == 'const'){
					$tags[$oneTag] = defined($val) ? constant($val) : 'Constant not defined : '.$val;
				}elseif($type == 'config'){
					if($val == 'sitename'){
						$tags[$oneTag] = ACYMAILING_J30 ? $jconfig->get($val) : $jconfig->getValue('config.'.$val);
					}
				}else{
					$tags[$oneTag] = JText::_($val);
				}
			}
		}

		$email->body = str_replace(array_keys($tags),$tags,$email->body);
		$email->altbody = str_replace(array_keys($tags),$tags,$email->altbody);
		$email->subject = str_replace(array_keys($tags),$tags,$email->subject);
	}


	function _ifstatement(&$email,$user){
		if(isset($this->foundtags[$email->mailid])) return;

		$match = '#{if:(.*)}(.*){/if}#Uis';
		$variables = array('subject','body','altbody');
		$found = false;
		foreach($variables as $var){
			if(empty($email->$var)) continue;
			$found = preg_match_all($match,$email->$var,$results[$var]) || $found;
			if(empty($results[$var][0])) unset($results[$var]);
		}

		if(!$found){
			$this->foundtags[$email->mailid] = false;
			return;
		}

		$app = JFactory::getApplication();

		static $a = false;

		$tags = array();
		foreach($results as $var => $allresults){
			foreach($allresults[0] as $i => $oneTag){
				if(isset($tags[$oneTag])) continue;
				if(!preg_match('#^([^=!<>~]+)(=|!=|<|>|&gt;|&lt;|~)([^=!<>~]+)$#i',$allresults[1][$i],$operators)){
					if($app->isAdmin()) acymailing_display('Operation not found : '.$allresults[1][$i],'error');
					$tags[$oneTag] = $allresults[2][$i];
					continue;
				};
				$field = trim($operators[1]);
				$prop = '';

				$operatorsParts = explode('.',$operators[1]);
				$operatorComp = 'acymailing';
				if(count($operatorsParts) == 2 AND in_array($operatorsParts[0],array('acymailing','joomla'))){
					$operatorComp = $operatorsParts[0];
					$field = $operatorsParts[1];
				}

				if($operatorComp == 'joomla'){
					if(!empty($user->userid)){
						if($field == 'gid' && ACYMAILING_J16){
							$db = JFactory::getDBO();
							$db->setQuery('SELECT group_id FROM #__user_usergroup_map WHERE user_id = '.intval($user->userid));
							$prop = implode(';',acymailing_loadResultArray($db));
						}else{
							$db = JFactory::getDBO();
							$db->setQuery('SELECT * FROM #__users WHERE id = '.intval($user->userid));
							$juser = $db->loadObject();
							if(isset($juser->{$field})){
								$prop = strtolower($juser->{$field});
							}else{
								if($app->isAdmin() && !$a) acymailing_display('User variable not set : '.$field.' in '.$allresults[1][$i],'error');
								$a = true;
							}
						}
					}
				}else{
					if(!isset($user->{$field})){
						if($app->isAdmin() && !$a) acymailing_display('User variable not set : '.$field.' in '.$allresults[1][$i],'error');
						$a = true;
					}else{
						$prop = strtolower($user->{$field});
					}
				}

				$tags[$oneTag] = '';
				$val = trim(strtolower($operators[3]));
				if($operators[2] == '=' AND ($prop == $val || in_array($prop,explode(';',$val)) || in_array($val,explode(';',$prop)))){
					$tags[$oneTag] = $allresults[2][$i];
				}elseif($operators[2] == '!=' AND $prop != $val){
					$tags[$oneTag] = $allresults[2][$i];
				}elseif(($operators[2] == '>' || $operators[2] == '&gt;') AND $prop > $val){
					$tags[$oneTag] = $allresults[2][$i];
				}elseif(($operators[2] == '<' || $operators[2] == '&lt;') AND $prop < $val){
					$tags[$oneTag] = $allresults[2][$i];
				}elseif($operators[2] == '~' AND strpos($prop,$val) !== false){
					$tags[$oneTag] = $allresults[2][$i];
				}
			}
		}

		foreach($results as $var => $allresults){
			$email->$var = str_replace(array_keys($tags),$tags,$email->$var);
		}
	}

	function _removetext(&$email){
		$removetext = $this->params->get('removetext','{reg},{/reg},{pub},{/pub}');
		if(!empty($removetext)){
			$removeArray = explode(',',$removetext);
			if(!empty($email->body)) $email->body = str_replace($removeArray,'',$email->body);
			if(!empty($email->altbody)) $email->altbody = str_replace($removeArray,'',$email->altbody);
		}
	}

	function _addfooter(&$email){
		$footer = $this->params->get('footer');
		if(!empty($footer)){
			if(strpos($email->body,'</body>')){
				$email->body = str_replace('</body>','<br/>'.$footer.'</body>',$email->body);
			}else{
				$email->body .= '<br/>'.$footer;
			}

			if(!empty($email->altbody)){
				$email->altbody .= "\n".$footer;
			}
		}
	}

	 function onAcyDisplayActions(&$type){
	 	$type['addqueue'] = JText::_('ADD_QUEUE');
	 	$type['removequeue'] = JText::_('REMOVE_QUEUE');

	 	$db = JFactory::getDBO();
		$db->setQuery("SELECT `mailid`,`subject`, `type` FROM `#__acymailing_mail` WHERE `type` NOT IN ('notification','autonews') OR `alias` = 'confirmation' ORDER BY `type`,`subject` ASC ");
		$allEmails = $db->loadObjectList();

		$emailsToDisplay = array();
		$typeNews = '';
		foreach($allEmails as $oneMail){
			if($oneMail->type != $typeNews){
				if(!empty($typeNews)) $emailsToDisplay[] = JHTML::_('select.option',  '</OPTGROUP>');
				$typeNews = $oneMail->type;
				if($oneMail->type == 'notification'){
					$label = JText::_('NOTIFICATIONS');
				}elseif($oneMail->type == 'news'){
					$label = JText::_('NEWSLETTERS');
				}elseif($oneMail->type == 'followup'){
					$label = JText::_('FOLLOWUP');
				}elseif($oneMail->type == 'welcome'){
					$label = JText::_('MSG_WELCOME');
				}elseif($oneMail->type == 'unsub'){
					$label = JText::_('MSG_UNSUB');
				}else{
					$label = $oneMail->type;
				}
				$emailsToDisplay[] = JHTML::_('select.option',  '<OPTGROUP>', $label );
			}
			$emailsToDisplay[] = JHTML::_('select.option', $oneMail->mailid, $oneMail->subject.' ('.$oneMail->mailid.')' );
		}
		$emailsToDisplay[] = JHTML::_('select.option',  '</OPTGROUP>');

	 	$addqueue = '<div id="action__num__addqueue">'.JHTML::_('select.genericlist',  $emailsToDisplay, "action[__num__][addqueue][mailid]", 'class="inputbox" size="1"').'<br /><label for="addqueuesenddate__num__">'.JText::_('SEND_DATE').' </label> <input type="text" value="{time}" id="addqueuesenddate__num__" name="action[__num__][addqueue][senddate]" /></div>';

	 	$allMessages = JHTML::_('select.option', 0, JText::_('ACY_ALL') );
	 	array_unshift($emailsToDisplay,$allMessages);
	 	$removequeue = '<div id="action__num__removequeue">'.JHTML::_('select.genericlist',  $emailsToDisplay, "action[__num__][removequeue][mailid]", 'class="inputbox" size="1"').'</div>';
	 	return $addqueue.$removequeue;
	 }

	 function onAcyProcessAction_addqueue($cquery,$action,$num){
	 	$action['mailid'] = intval($action['mailid']);
	 	if(empty($action['mailid'])) return 'mailid not valid';

	 	$action['senddate'] = acymailing_replaceDate($action['senddate']);
	 	if(!is_numeric($action['senddate'])) $action['senddate'] = acymailing_getTime($action['senddate']);
	 	if(empty($action['senddate'])) return 'send date not valid';

	 	$query = 'INSERT IGNORE INTO `#__acymailing_queue` (`mailid`,`subid`,`senddate`,`priority`) '.$cquery->getQuery(array($action['mailid'],'sub.`subid`',$action['senddate'],'2'));
	 	$db = JFactory::getDBO();
	 	$db->setQuery($query);
	 	$db->query();
	 	return JText::sprintf('ADDED_QUEUE',$db->getAffectedRows());

	 }

	 function onAcyProcessAction_removequeue($cquery,$action,$num){
	 	$action['mailid'] = intval($action['mailid']);
	 	if(!empty($action['mailid'])) $cquery->where[] = 'queueremove.mailid = '.$action['mailid'];

		$query = 'DELETE queueremove.* FROM `#__acymailing_queue` as queueremove ';
		$query .= 'JOIN `#__acymailing_subscriber` as sub ON queueremove.subid = sub.subid ';
		if(!empty($cquery->join)) $query .= ' JOIN '.implode(' JOIN ',$cquery->join);
		if(!empty($cquery->leftjoin)) $query .= ' LEFT JOIN '.implode(' LEFT JOIN ',$cquery->leftjoin);
		if(!empty($cquery->where)) $query .= ' WHERE ('.implode(') AND (',$cquery->where).')';

	 	$db = JFactory::getDBO();
	 	$db->setQuery($query);
	 	$db->query();
	 	return JText::sprintf('SUCC_DELETE_ELEMENTS',$db->getAffectedRows());
	 }

}//endclass
