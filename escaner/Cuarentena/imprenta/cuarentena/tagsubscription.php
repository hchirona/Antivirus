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

class plgAcymailingTagsubscription extends JPlugin
{
	var $listunsubscribe = false;

	function plgAcymailingTagsubscription(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('acymailing', 'tagsubscription');
			$this->params = new JParameter( $plugin->params );
		}
	}

	 function acymailing_getPluginType() {

	 	$onePlugin = new stdClass();
	 	$onePlugin->name = JText::_('SUBSCRIPTION');
	 	$onePlugin->function = 'acymailingtagsubscription_show';
	 	$onePlugin->help = 'plugin-tagsubscription';

	 	return $onePlugin;
	 }

	function onAcyDisplayActions(&$type){
	 	$type['list'] = JText::_('ACYMAILING_LIST');
		$status = array();
		$status[] = JHTML::_('select.option',1,JText::_('SUBSCRIBE_TO'));
		$status[] = JHTML::_('select.option',0,JText::_('REMOVE_FROM'));

		$lists = $this->_getLists();
		$otherlists = array();
		if(acymailing_level(3)){
			$db = JFactory::getDBO();
			$db->setQuery('SELECT b.listid, b.name FROM #__acymailing_listcampaign as a LEFT JOIN #__acymailing_list as b on a.listid = b.listid GROUP BY b.listid ORDER BY b.ordering ASC');
			$otherlists = $db->loadObjectList('listid');
		}

		$listsdrop = array();
		foreach($lists as $oneList){
			$listsdrop[] = JHTML::_('select.option',$oneList->listid,$oneList->name);
			if(!empty($otherlists[$oneList->listid])) $listsdrop[] = JHTML::_('select.option',$oneList->listid.'_campaign',$otherlists[$oneList->listid]->name.' + '.JText::_('CAMPAIGN'));
		}

	 	return '<div id="action__num__list">'.JHTML::_('select.genericlist',   $status, "action[__num__][list][status]", 'class="inputbox" size="1"', 'value', 'text').' '.JHTML::_('select.genericlist',   $listsdrop, "action[__num__][list][selectedlist]", 'class="inputbox" size="1"', 'value', 'text').'</div>';
	 }

	 function _getLists(){
	 	if(!empty($this->allLists)) return $this->allLists;
	 	$list = acymailing_get('class.list');
		$this->allLists = $list->getLists();
		return $this->allLists;
	 }

	 function onAcyDisplayFilters(&$type,$context="massactions"){

		if($this->params->get('displayfilter_'.$context,true) == false) return;

	 	$type['list'] = JText::_('ACYMAILING_LIST');
	 	$status = acymailing_get('type.statusfilterlist');
	 	$status->extra = 'onchange="countresults(__num__)"';

		$lists = $this->_getLists();
		$listsdrop = array();
		foreach($lists as $oneList){
			$listsdrop[] = JHTML::_('select.option',$oneList->listid,$oneList->name);
		}

	 	$filter = '<div id="filter__num__list">'.$status->display("filter[__num__][list][status]",1,false).' '.JHTML::_('select.genericlist',   $listsdrop, "filter[__num__][list][selectedlist]", 'class="inputbox" style="max-width:200px" size="1" onchange="countresults(__num__)"', 'value', 'text');
	 	$filter .= '<br /><input type="text" name="filter[__num__][list][subdateinf]" onchange="countresults(__num__)" style="width:60px;" /> < '.JText::_('SUBSCRIPTION_DATE').' < <input type="text" name="filter[__num__][list][subdatesup]" onchange="countresults(__num__)" style="width:60px;" /></div>';
	 	return $filter;
	 }

	 function onAcyProcessFilter_list(&$query,$filter,$num){
	 	$otherconditions = '';
	 	if(!empty($filter['subdateinf'])){
				$filter['subdateinf'] = acymailing_replaceDate($filter['subdateinf']);
			if(!is_numeric($filter['subdateinf'])) $filter['subdateinf'] = strtotime($filter['subdateinf']);
			if(!empty($filter['subdateinf'])) $otherconditions .= ' AND list'.$num.'.subdate > '.$filter['subdateinf'];
	 	}
	 	if(!empty($filter['subdatesup'])){
	 		$filter['subdatesup'] = acymailing_replaceDate($filter['subdatesup']);
			if(!is_numeric($filter['subdatesup'])) $filter['subdatesup'] = strtotime($filter['subdatesup']);
			if(!empty($filter['subdatesup'])) $otherconditions .= ' AND list'.$num.'.subdate < '.$filter['subdatesup'];
	 	}

	 	$query->leftjoin['list'.$num] = '#__acymailing_listsub AS list'.$num.' ON sub.subid = list'.$num.'.subid AND list'.$num.'.listid = '.intval($filter['selectedlist']).$otherconditions;
		if($filter['status'] == -2){
			$query->where[] = 'list'.$num.'.listid IS NULL';
		}else{
			$query->where[] = 'list'.$num.'.status = '.intval($filter['status']);
		}
	 }

 	function onAcyProcessFilterCount_list(&$query,$filter,$num){
		$this->onAcyProcessFilter_list($query,$filter,$num);
		return JText::sprintf('SELECTED_USERS',$query->count());
	}

	function onAcyProcessAction_list($cquery,$action,$num){
		$listid = $action['selectedlist'];
		$listClass = acymailing_get('class.list');
		if(is_numeric($listid)){
			if(empty($action['status'])){
				$query = 'DELETE listremove.* FROM '.acymailing_table('listsub').' as listremove ';
				$query .= 'JOIN #__acymailing_subscriber as sub ON listremove.subid = sub.subid ';
				if(!empty($cquery->join)) $query .= ' JOIN '.implode(' JOIN ',$cquery->join);
				if(!empty($cquery->leftjoin)) $query .= ' LEFT JOIN '.implode(' LEFT JOIN ',$cquery->leftjoin);
				$query .= ' WHERE listremove.listid = '.$listid;
				if(!empty($cquery->where)) $query .= ' AND ('.implode(') AND (',$cquery->where).')';
			}else{
				$query = 'INSERT IGNORE INTO '.acymailing_table('listsub').' (listid,subid,subdate,status) ';
				$query .= $cquery->getQuery(array($listid,'sub.subid',time(),1));
			}

			$cquery->db->setQuery($query);
			$cquery->db->query();
			$nbsubscribed = $cquery->db->getAffectedRows();

			$myList = $listClass->get($listid);
			if(empty($action['status'])){
				return JText::sprintf('IMPORT_REMOVE',$nbsubscribed,'<b><i>'.$myList->name.'</i></b>');
			}else{
				return JText::sprintf('IMPORT_SUBSCRIBE_CONFIRMATION',$nbsubscribed,'<b><i>'.$myList->name.'</i></b>');
			}
		}

		$listid = intval($listid);
		$myList = $listClass->get($listid);
		if(empty($action['status'])){
			$query = 'SELECT listremove.`subid` FROM #__acymailing_listsub as listremove';
			$query .= ' JOIN #__acymailing_subscriber as sub ON listremove.subid = sub.subid ';
			if(!empty($cquery->join)) $query .= ' JOIN '.implode(' JOIN ',$cquery->join);
			if(!empty($cquery->leftjoin)) $query .= ' LEFT JOIN '.implode(' LEFT JOIN ',$cquery->leftjoin);
			$query .= ' WHERE listremove.listid = '.$listid;
			if(!empty($cquery->where)) $query .= ' AND ('.implode(') AND (',$cquery->where).')';
		}else{
			$query = 'SELECT sub.`subid` FROM #__acymailing_subscriber as sub';
			$query .= ' LEFT JOIN #__acymailing_listsub as listsubscribe ON listsubscribe.subid = sub.subid AND listsubscribe.listid = '.$listid;
			if(!empty($cquery->join)) $query .= ' JOIN '.implode(' JOIN ',$cquery->join);
			if(!empty($cquery->leftjoin)) $query .= ' LEFT JOIN '.implode(' LEFT JOIN ',$cquery->leftjoin);
			$query .= ' WHERE listsubscribe.subid IS NULL';
			if(!empty($cquery->where)) $query .= ' AND ('.implode(') AND (',$cquery->where).')';
		}

		$cquery->db->setQuery($query);
		$subids =  acymailing_loadResultArray($cquery->db);
		if(!empty($subids)){
			$listsubClass = acymailing_get('class.listsub');
			$listsubClass->checkAccess = false;
			$listsubClass->sendNotif = false;
			$listsubClass->sendConf = false;
			foreach($subids as $subid){
				if(empty($action['status'])) $listsubClass->removeSubscription($subid,array($listid));
				else $listsubClass->addSubscription($subid,array('1' => array($listid)));
			}
		}

		$nbsubscribed = count($subids);
		if(empty($action['status'])){
			return JText::sprintf('IMPORT_REMOVE',$nbsubscribed,'<b><i>'.$myList->name.'</i></b>');
		}else{
			return JText::sprintf('IMPORT_SUBSCRIBE_CONFIRMATION',$nbsubscribed,'<b><i>'.$myList->name.'</i></b>');
		}
	}


	 function acymailingtagsubscription_show(){

		$others = array();
		$others['unsubscribe'] = array('name'=> JText::_('UNSUBSCRIBE_LINK'),'default'=>JText::_('UNSUBSCRIBE',true));
		$others['modify'] = array('name'=> JText::_('MODIFY_SUBSCRIPTION_LINK'), 'default'=>JText::_('MODIFY_SUBSCRIPTION',true));
		$others['confirm'] = array('name'=> JText::_('CONFIRM_SUBSCRIPTION_LINK'), 'default'=>JText::_('CONFIRM_SUBSCRIPTION',true));

?>
		<script language="javascript" type="text/javascript">
		<!--
			var selectedTag = '';
			function changeTag(tagName){
				selectedTag = tagName;
				defaultText = new Array();
<?php
				$k = 0;
				foreach($others as $tagname => $tag){
					echo "document.getElementById('tr_$tagname').className = 'row$k';";
					echo "defaultText['$tagname'] = '".$tag['default']."';";
				}
				$k = 1-$k;
?>
				document.getElementById('tr_'+tagName).className = 'selectedrow';
				document.adminForm.tagtext.value = defaultText[tagName];
				setSubscriptionTag();
			}

			function setSubscriptionTag(){
				setTag('{'+selectedTag+'}'+document.adminForm.tagtext.value+'{/'+selectedTag+'}');
			}

		//-->
		</script>
<?php

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration("window.addEvent('domready', function(){ changeTag('unsubscribe'); });");

		$text = JText::_('FIELD_TEXT').' : <input type="text" name="tagtext" size="100px" onchange="setSubscriptionTag();"><br/><br/>';

		$text .= '<table class="adminlist table table-striped table-hover" cellpadding="1">';

		$k = 0;
		foreach($others as $tagname => $tag){
			$text .= '<tr style="cursor:pointer" class="row'.$k.'" onclick="changeTag(\''.$tagname.'\');" id="tr_'.$tagname.'" ><td class="acytdcheckbox"></td><td>'.$tag['name'].'</td></tr>';
			$k = 1-$k;
		}
		$text .= '</table>';

		echo $text;
	 }

	function acymailing_replaceusertags(&$email,&$user,$send = true){
		$this->_replacesubscriptiontags($email,$user);
		$this->_replacelisttags($email,$user);
	}

	function _replacelisttags(&$email,&$user){
		$match = '#{list:(.*)}#Ui';
		$variables = array('subject','body','altbody');
		$found = false;
		foreach($variables as $var){
			if(empty($email->$var)) continue;
			$found = preg_match_all($match,$email->$var,$results[$var]) || $found;
			if(empty($results[$var][0])) unset($results[$var]);
		}

		if(!$found) return;

		$tags = array();
		foreach($results as $var => $allresults){
			foreach($allresults[0] as $i => $oneTag){
				if(isset($tags[$oneTag])) continue;
				$arguments = explode('|',strip_tags($allresults[1][$i]));
				$parameter = new stdClass();
				$method = '_list'.trim(strtolower($arguments[0]));
				for($i=1;$i<count($arguments);$i++){
					$args = explode(':',$arguments[$i]);
					$arg0 = trim($args[0]);
					if(isset($args[1])){
						$parameter->$arg0 = $args[1];
					}else{
						$parameter->$arg0 = true;
					}
				}

				if(method_exists($this,$method)){
					$tags[$oneTag] = $this->$method($email,$user,$parameter);
				}else{
					$tags[$oneTag] = 'Method not found : '.$method;
				}
			}
		}

		foreach(array_keys($results) as $var){
			$email->$var = str_replace(array_keys($tags),$tags,$email->$var);
		}

	}

	function _getattachedlistid($mailid,$subid){
		$db = JFactory::getDBO();
		if(!empty($subid)){
			$db->setQuery('SELECT a.listid FROM #__acymailing_listsub as a JOIN #__acymailing_listmail as b ON a.listid = b.listid WHERE a.subid = '.$subid.' AND b.mailid = '.$mailid.' ORDER BY a.status DESC LIMIT 1');
			$listid = $db->loadResult();
			if(!empty($listid)) return $listid;
		}

		$db->setQuery('SELECT a.listid FROM #__acymailing_listmail as a JOIN #__acymailing_list as b ON a.listid = b.listid WHERE a.mailid = '.$mailid.' ORDER BY b.published DESC , b.visible DESC LIMIT 1');
		$listid = $db->loadResult();
		if(!empty($listid)) return $listid;

		$db->setQuery('SELECT a.listid FROM #__acymailing_list as a WHERE a.welmailid = '.$mailid.' OR unsubmailid = '.$mailid.' ORDER BY a.published DESC , a.visible DESC LIMIT 1');
		$listid = $db->loadResult();
		if(!empty($listid)) return $listid;

		$allLists = array_merge(JRequest::getVar('subscription','','','array'),explode(',',JRequest::getVar('hiddenlists','','','string')));
		if(!empty($allLists)){
			foreach($allLists as $listid){
				if(!empty($listid)) return intval($listid);
			}
		}

		if(!empty($subid)){
			$db->setQuery('SELECT a.listid FROM #__acymailing_listsub as a JOIN #__acymailing_list as b ON a.listid = b.listid WHERE a.subid = '.$subid.' ORDER BY b.published DESC , b.visible DESC LIMIT 1');
			$listid = $db->loadResult();
			if(!empty($listid)) return $listid;
		}
	}



	function _listcount(&$email,&$user,&$parameter){
		if(!isset($parameter->listid)){
			$listid = $this->_getattachedlistid($email->mailid,$user->subid);
		}else{
			$listid = $parameter->listid;
		}


		$db = JFactory::getDBO();
		if(empty($listid)){
			$db->setQuery('SELECT COUNT(subid) FROM #__acymailing_subscriber');
		}else{
			$db->setQuery('SELECT COUNT(subid) FROM #__acymailing_listsub WHERE listid = '.intval($listid).' AND status = 1');
		}

		return $db->loadResult();
	}

	function _listname(&$email,&$user,&$parameter){
		$listid = $this->_getattachedlistid($email->mailid,$user->subid);
		if(empty($listid)) return "No list => no name!";

		$db = JFactory::getDBO();
		$db->setQuery('SELECT name FROM #__acymailing_list WHERE listid = '.$listid);
		return $db->loadResult();
	}

	function _listid(&$email,&$user,&$parameter){
		$listid = $this->_getattachedlistid($email->mailid,$user->subid);
		if(empty($listid)) return "No list => no ID!";

		return $listid;
	}

	function _replacesubscriptiontags(&$email,&$user){
		$match = '#(?:{|%7B)(modify|confirm|unsubscribe)(?:}|%7D)(.*)(?:{|%7B)/(modify|confirm|unsubscribe)(?:}|%7D)#Uis';
		$variables = array('subject','body','altbody');
		$found = false;
		foreach($variables as $var){
			if(empty($email->$var)) continue;
			$found = preg_match_all($match,$email->$var,$results[$var]) || $found;
			if(empty($results[$var][0])) unset($results[$var]);
		}

		if(!$found) return;

		$tags = array();
		$this->listunsubscribe = false;
		foreach($results as $var => $allresults){
			foreach($allresults[0] as $i => $oneTag){
				if(isset($tags[$oneTag])) continue;
				$tags[$oneTag] = $this->replaceSubscriptionTag($allresults,$i,$user,$email);
			}
		}

		foreach(array_keys($results) as $var){
			$email->$var = str_replace(array_keys($tags),$tags,$email->$var);
		}
	}

	function replaceSubscriptionTag(&$allresults,$i,&$user,&$email){
		if(empty($user->subid)){
			return '';
		}

		if(empty($user->key)){
			$user->key = md5(substr($user->email,0,strpos($user->email,'@')).time());
			$db = JFactory::getDBO();
			$db->setQuery('UPDATE '.acymailing_table('subscriber').' SET `key`= '.$db->Quote($user->key).' WHERE subid = '.(int) $user->subid.' LIMIT 1');
			$db->query();
		}

		$config = acymailing_config();
		$itemId = $config->get('itemid',0);
		$item = empty($itemId) ? '' : '&Itemid='.$itemId;

		if($allresults[1][$i] == 'confirm'){ //confirm your subscription link
			$itemId = $this->params->get('confirmitemid',0);
			if(!empty($itemId)) $item = '&Itemid='.$itemId;
			$myLink = acymailing_frontendLink('index.php?subid='.$user->subid.'&option=com_acymailing&ctrl=user&task=confirm&key='.$user->key.$item,(bool) $this->params->get('confirmtemplate',false));
			if(empty($allresults[2][$i])) return $myLink;
			return '<a target="_blank" href="'.$myLink.'">'.$allresults[2][$i].'</a>';
		}elseif($allresults[1][$i] == 'modify'){ //modify your subscription link
			$itemId = $this->params->get('modifyitemid',0);
			if(!empty($itemId)) $item = '&Itemid='.$itemId;
			$myLink = acymailing_frontendLink('index.php?subid='.$user->subid.'&option=com_acymailing&ctrl=user&task=modify&key='.$user->key.$item,(bool) $this->params->get('modifytemplate',false));
			if(empty($allresults[2][$i])) return $myLink;
			return '<a style="text-decoration:none;" target="_blank" href="'.$myLink.'"><span class="acymailing_unsub">'.$allresults[2][$i].'</span></a>';
		}//unsubscribe link

		$itemId = $this->params->get('unsubscribeitemid',0);
		if(!empty($itemId)) $item = '&Itemid='.$itemId;
		$myLink = acymailing_frontendLink('index.php?subid='.$user->subid.'&option=com_acymailing&ctrl=user&task=out&mailid='.$email->mailid.'&key='.$user->key.$item,(bool) $this->params->get('unsubscribetemplate',false));

		if(!$this->listunsubscribe && $this->params->get('listunsubscribe',0) && method_exists($email,'addCustomHeader')){
			$this->listunsubscribe = true;
			$mailto = $this->params->get('listunsubscribeemail');
			if(empty($mailto)) $mailto = @$email->replyemail;
			if(empty($mailto)) $mailto = $config->get('reply_email');
			$email->addCustomHeader( 'List-Unsubscribe: <'.$myLink.'>, <mailto:'.$mailto.'?subject=unsubscribe_user_'.$user->subid.'>' );
		}
		if(empty($allresults[2][$i])) return $myLink;
		return '<a style="text-decoration:none;" target="_blank" href="'.$myLink.'"><span class="acymailing_unsub">'.$allresults[2][$i].'</span></a>';
	}
}//endclass
