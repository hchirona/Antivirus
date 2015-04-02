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

class importHelper{

	var $importUserInLists = array();
	var $totalInserted = 0;
	var $totalTry = 0;
	var $totalValid = 0;
	var $allSubid = array();
	var $db;
	var $dispatcher;
	var $forceconfirm = false;
	var $charsetConvert;
	var $generatename = true;
	var $overwrite = false;
	var $removeSep = 0;
	var $dispresults = true;

	var $tablename = '';
	var $equFields = array();
	var $dbwhere = array(); //handle where on import via filter to only import new users for example

	var $subscribedUsers = array();

	function importHelper(){
		acymailing_increasePerf();

		$this->db = JFactory::getDBO();

		JPluginHelper::importPlugin('acymailing');
		$this->dispatcher = JDispatcher::getInstance();
	}

	function database($onlyimport = false){
		$app = JFactory::getApplication();

		$table = empty($this->tablename) ? trim(JRequest::getString('tablename')) : $this->tablename;

		if(empty($table)){
			$listTables = $this->db->getTableList();
			$app->enqueueMessage(JText::sprintf('SPECIFYTABLE',implode(' | ',$listTables)),'notice');
			return false;
		}

		if(empty($this->tablename)){
			$newConfig = new stdClass();
			$newConfig->import_db_table = trim(JRequest::getString('tablename'));
			$newConfig->import_db_fields = serialize(JRequest::getVar('fields',array()));

			$config = acymailing_config();
			$config->save($newConfig);
		}

		$fields = acymailing_getColumns($table);
		if(empty($fields)){
			$listTables = $this->db->getTableList();
			$app->enqueueMessage(JText::sprintf('SPECIFYTABLE',implode(' | ',$listTables)),'notice');
			return false;
		}

		$fields = array_keys($fields);
		$equivalentFields = empty($this->equFields) ? JRequest::getVar('fields',array()) : $this->equFields;

		if(empty($equivalentFields['email'])){
			$app->enqueueMessage(JText::_('SPECIFYFIELDEMAIL'),'notice');
			return false;
		}

		$select = array();
		foreach($equivalentFields as $acyField => $tableField){
			$tableField = trim($tableField);
			if(empty($tableField)) continue;
			if(!in_array($tableField,$fields)){
				$app->enqueueMessage(JText::sprintf('SPECIFYFIELD',$tableField,implode(' | ',$fields)),'notice');
				return false;
			}
			$select['`'.$acyField.'`'] = '`'.$tableField.'`';
		}

		if(empty($select['`created`'])){ $select['`created`'] = time(); }

		$query = 'INSERT IGNORE INTO `#__acymailing_subscriber` ('.implode(' , ',array_keys($select)).') SELECT '.implode(' , ',$select).' FROM '.$table.' WHERE '.$select['`email`'].' LIKE \'%@%\'';
		if(!empty($this->dbwhere)) $query .= ' AND ( '.implode(' ) AND (',$this->dbwhere).' )';

		$this->db->setQuery($query);
		$this->db->query();
		$affectedRows = $this->db->getAffectedRows();

		$app->enqueueMessage(JText::sprintf('IMPORT_NEW',$affectedRows));

		if($onlyimport) return true;

		$query = 'SELECT b.subid FROM '.$table.' as a JOIN '.acymailing_table('subscriber').' as b on a.'.$select['`email`'].' = b.`email`';
		$this->db->setQuery($query);
		$this->allSubid = acymailing_loadResultArray($this->db);

		$this->_subscribeUsers();
		$this->_displaySubscribedResult();

		return true;
	}

	function textarea(){
		$this->forceconfirm = JRequest::getInt('import_confirmed_textarea');
		$this->generatename = JRequest::getInt('generatename_textarea');
		$this->overwrite = JRequest::getInt('overwriteexisting_textarea');
		$content = JRequest::getString('textareaentries');

		$result = $this->_handleContent($content);
		$this->_displaySubscribedResult();
		return $result;
	}

	function file(){
		$app = JFactory::getApplication();
		$importFile =  JRequest::getVar( 'importfile', array(), 'files','array');

		if(empty($importFile['name'])){
			$app->enqueueMessage(JText::_('BROWSE_FILE'),'notice');
			return false;
		}

		$this->forceconfirm = JRequest::getInt('import_confirmed');
		$this->charsetConvert = JRequest::getString('charsetconvert','');
		$this->generatename = JRequest::getInt('generatename');
		$this->overwrite = JRequest::getInt('overwriteexisting');

		jimport('joomla.filesystem.file');

		$config =& acymailing_config();

		$uploadFolder = JPath::clean(html_entity_decode($config->get('uploadfolder')));
		$uploadFolder = trim($uploadFolder,DS.' ').DS;
		$uploadPath = JPath::clean(ACYMAILING_ROOT.$uploadFolder);

		acymailing_createDir($uploadPath);

		if(!is_writable($uploadPath)){
			@chmod($uploadPath,'0755');
			if(!is_writable($uploadPath)){
				$app->enqueueMessage(JText::sprintf( 'WRITABLE_FOLDER',$uploadPath), 'notice');
			}
		}

		$attachment = new stdClass();
		$attachment->filename = strtolower(JFile::makeSafe($importFile['name']));
		$attachment->size = $importFile['size'];

		if(!preg_match('#\.('.str_replace(array(',','.'),array('|','\.'),$config->get('allowedfiles')).')$#Ui',$attachment->filename,$extension) || preg_match('#\.(php.?|.?htm.?|pl|py|jsp|asp|sh|cgi)$#Ui',$attachment->filename)){
			$app->enqueueMessage(JText::sprintf( 'ACCEPTED_TYPE',substr($attachment->filename,strrpos($attachment->filename,'.')+1),$config->get('allowedfiles')), 'notice');
			return false;
		}

		$attachment->filename = str_replace(array('.',' '),'_',substr($attachment->filename,0,strpos($attachment->filename,$extension[0]))).$extension[0];

		if(!JFile::upload($importFile['tmp_name'], $uploadPath . $attachment->filename)){
			if ( !move_uploaded_file($importFile['tmp_name'], $uploadPath . $attachment->filename)) {
				$app->enqueueMessage(JText::sprintf( 'FAIL_UPLOAD','<b><i>'.$importFile['tmp_name'].'</i></b>','<b><i>'.$uploadPath . $attachment->filename.'</i></b>'), 'error');
			}
		}

		$contentFile = file_get_contents($uploadPath . $attachment->filename);

		if(!$contentFile){
			$app->enqueueMessage(JText::sprintf( 'FAIL_OPEN','<b><i>'.$uploadPath . $attachment->filename.'</i></b>'), 'error');
			return false;
		};

		unlink($uploadPath.$attachment->filename);

		$toTest = array();
		if(empty($this->charsetConvert)){
			$encodingHelper = acymailing_get('helper.encoding');
			$this->charsetConvert = $encodingHelper->detectEncoding($contentFile);
		}

		$result = $this->_handleContent($contentFile);
		$this->_displaySubscribedResult();
		return $result;
	}

	function _handleContent(&$contentFile){
		$success = true;
		$app = JFactory::getApplication();

		$contentFile = str_replace(array("\r\n","\r"),"\n",$contentFile);

		$importLines = explode("\n", $contentFile);

		$i = 0;
		$this->header = '';
		$this->allSubid = array();
		while(empty($this->header) && $i < 10){
			$this->header = trim($importLines[$i]);
			$i++;
		}

		if(strpos($this->header,'@') && !strpos($this->header,',') && !strpos($this->header,';') && !strpos($this->header,"\t")){
			$this->header = 'email';
			$i--;
		}

		if(!$this->_autoDetectHeader()){
			$app->enqueueMessage(JText::sprintf('IMPORT_HEADER',$this->header),'error');
			$app->enqueueMessage(JText::_('IMPORT_EMAIL'),'error');
			$app->enqueueMessage(JText::_('IMPORT_EXAMPLE'),'error');
			return false;
		}

		$numberColumns = count($this->columns);

		$userHelper = acymailing_get('helper.user');

		$importUsers = array();

		$encodingHelper = acymailing_get('helper.encoding');


		while (isset($importLines[$i])) {

			if(!empty($this->charsetConvert)){
				$importLines[$i] = $encodingHelper->change($importLines[$i],$this->charsetConvert,'UTF-8');
			}

			$data = explode($this->separator,rtrim(trim($importLines[$i]),$this->separator));

			if(!empty($this->removeSep)){
				for($b = $numberColumns+$this->removeSep-1;$b >= $numberColumns;$b-- ){
					if(isset($data[$b]) AND (strlen($data[$b])==0 || $data[$b] == ' ')){
						unset($data[$b]);
					}
				}
			}

			$i++;
			if(empty($importLines[$i-1])) continue;

			$this->totalTry++;
			if(count($data) > $numberColumns){
				$copy = $data;
				foreach($copy as $oneelem => $oneval){
					if(!empty($oneval[0]) AND $oneval[0] == '"' AND $oneval[strlen($oneval)-1] != '"' AND isset($copy[$oneelem+1]) AND $copy[$oneelem+1][strlen($copy[$oneelem+1])-1] == '"'){
						$data[$oneelem] = $copy[$oneelem].$this->separator.$copy[$oneelem+1];
						unset($data[$oneelem+1]);
					}
				}
				$data = array_values($data);
			}

			if(count($data) < $numberColumns){
				for($a=count($data);$a<$numberColumns;$a++){
					$data[$a] = '';
				}
			}

			if(count($data) != $numberColumns){
				$success = false;
				static $errorcount = 0;
				if(empty($errorcount)){
					$app->enqueueMessage(JText::sprintf('IMPORT_ARGUMENTS',$numberColumns),'error');
				}
				$errorcount++;
				if($errorcount<20){
					$app->enqueueMessage(JText::sprintf('IMPORT_ERRORLINE','<b><i>'.$importLines[$i-1].'</i></b>'),'notice');
				}elseif($errorcount == 20){
					$app->enqueueMessage('...','notice');
				}

				if($this->totalTry == 1) return false;
				continue;
			}

			$newUser = new stdClass();
			foreach($data as $num => $value){
				$field = $this->columns[$num];

				if($field == 'listids'){
					$liststosub = explode('-',trim($value,'\'" '));
					foreach($liststosub as $onelistid){
						$this->importUserInLists[$onelistid][] = $this->db->Quote($newUser->email);
					}
					continue;
				}

				$newUser->$field = trim($value,'\'" ');
			}

			$newUser->email = trim(str_replace(array(' ',"\t"),'',$encodingHelper->change($newUser->email,'UTF-8','ISO-8859-1')));

			if(!$userHelper->validEmail($newUser->email)){
				$success = false;
				static $errorcountfail = 0;
				$errorcountfail++;
				if($errorcountfail<50){
					$app->enqueueMessage(JText::sprintf('NOT_VALID_EMAIL','<b><i>'.$newUser->email.'</i></b>').' | '.($i-1).' : '.$importLines[$i-1],'notice');
				}elseif($errorcountfail == 50){
					$app->enqueueMessage('...','notice');
				}
				continue;
			}

			unset($newUser->subid); unset($newUser->userid);

			$importUsers[] = $newUser;
			$this->totalValid++;

			if( $this->totalValid%50 == 0){
				$this->_insertUsers($importUsers);
				$importUsers = array();
			}

		}

		$this->_insertUsers($importUsers);

		if($this->dispresults){
			$app->enqueueMessage(JText::sprintf('IMPORT_REPORT',$this->totalTry,$this->totalInserted,$this->totalTry - $this->totalValid,$this->totalValid - $this->totalInserted));
		}

		$this->_subscribeUsers();

		return $success;

	}

	function _subscribeUsers(){

		if(empty($this->allSubid)) return true;

		$subdate = time();

		$listClass= acymailing_get('class.list');

		if(empty($this->importUserInLists)){
			$lists = JRequest::getVar('importlists',array());
			foreach($lists as $listid => $val){
				if(!empty($val)){
					$nbsubscribed = 0;
					$listid = (int) $listid;
					$query = 'INSERT IGNORE INTO '.acymailing_table('listsub').' (listid,subid,subdate,status) VALUES ';
					$b = 0;
					foreach($this->allSubid as $subid){
						$b++;
						if($b>200){
							$query = rtrim($query,',');
							$this->db->setQuery($query);
							$this->db->query();
							$nbsubscribed += $this->db->getAffectedRows();
							$b = 0;
							$query = 'INSERT IGNORE INTO '.acymailing_table('listsub').' (listid,subid,subdate,status) VALUES ';
						}
						$query .= "($listid,$subid,$subdate,1),";
					}
					$query = rtrim($query,',');
					$this->db->setQuery($query);
					$this->db->query();
					$nbsubscribed += $this->db->getAffectedRows();
					if(isset($this->subscribedUsers[$listid])){
						$this->subscribedUsers[$listid]->nbusers += $nbsubscribed;
					}else{
						$myList = $listClass->get($listid);
						$this->subscribedUsers[$listid] = $myList;
						$this->subscribedUsers[$listid]->nbusers = $nbsubscribed;
					}
				}
			}
		}else{
			foreach($this->importUserInLists as $listid => $arrayEmails){
				if(!empty($listid)){
					$listid = (int) $listid;
					$query = 'INSERT IGNORE INTO '.acymailing_table('listsub').' (listid,subid,subdate,status) ';
					$query .= "SELECT $listid,`subid`,$subdate,1 FROM ".acymailing_table('subscriber')." WHERE `email` IN (";
					$query .= implode(',',$arrayEmails).')';
					$this->db->setQuery($query);
					$this->db->query();

					$nbsubscribed = $this->db->getAffectedRows();
					if(isset($this->subscribedUsers[$listid])){
						$this->subscribedUsers[$listid]->nbusers += $nbsubscribed;
					}else{
						$myList = $listClass->get($listid);
						$this->subscribedUsers[$listid] = $myList;
						$this->subscribedUsers[$listid]->nbusers = $nbsubscribed;
					}

				}
			}
		}


		return true;

	}

	function _displaySubscribedResult(){
		$app = JFactory::getApplication();
		foreach($this->subscribedUsers as $myList){
			 $app->enqueueMessage(JText::sprintf('IMPORT_SUBSCRIBE_CONFIRMATION',$myList->nbusers,'<b><i>'.$myList->name.'</i></b>'));
		}
	}

	function _insertUsers($users){
		if(empty($users)) return true;

		if($this->overwrite){
			$emailstoload = array();
			foreach($users as $a => $oneUser){
				$emailstoload[] = $this->db->Quote($oneUser->email);
			}
			$this->db->setQuery('SELECT * FROM `#__acymailing_subscriber` WHERE `email` IN ('.implode(',',$emailstoload).')');
			$subids = $this->db->loadObjectList('email');
			$dataoneuser = @array_keys(get_object_vars(reset($subids)));
			foreach($users as $a => $oneUser){
				$users[$a]->subid = (!empty($subids[$oneUser->email]->subid)) ? $subids[$oneUser->email]->subid : 'NULL';
				if(empty($dataoneuser)) continue;
				foreach($dataoneuser as $oneField){
					if(!isset($users[$a]->$oneField)) $users[$a]->$oneField = @$subids[$oneUser->email]->$oneField;
				}
			}
			$this->totalInserted -= (count($subids)*2);
		}

		foreach($users as $a => $oneUser){
			$this->_checkData($users[$a]);
		}

		$columns = reset($users);
		$query = $this->overwrite ? 'REPLACE' : 'INSERT IGNORE';
		$query .= ' INTO '.acymailing_table('subscriber').' (`'.implode('`,`',array_keys(get_object_vars($columns))).'`) VALUES (';
		$values = array();
		$allemails = array();
		foreach($users as $a => $oneUser){
			$value = array();
			$this->dispatcher->trigger('onAcyBeforeUserImport',array(&$oneUser));
			foreach($oneUser as $map => $oneValue){
				if($map != 'subid'){
					$value[] = $this->db->Quote($oneValue);
				}else{
					$value[] = $oneValue;
				}
				if($map == 'email'){
					$allemails[] = $this->db->Quote($oneValue);
				}
			}
			$values[] = implode(',',$value);
		}
		$query .= implode('),(',$values).')';

		$this->db->setQuery($query);
		$this->db->query();
		$this->totalInserted += $this->db->getAffectedRows();

		$this->db->setQuery('SELECT subid FROM '.acymailing_table('subscriber').' WHERE email IN ('.implode(',',$allemails).')');

		$this->allSubid = array_merge($this->allSubid,acymailing_loadResultArray($this->db));

		return true;
	}


	function _checkData(&$user){
		if(empty($user->created)) $user->created = time();
		elseif(!is_numeric($user->created)) $user->created = strtotime($user->created);

		if(!isset($user->accept) || strlen($user->accept) == 0) $user->accept = 1;
		if(!isset($user->enabled) || strlen($user->enabled) == 0) $user->enabled = 1;
		if(!isset($user->html) || strlen($user->html) == 0) $user->html = 1;

		if(!empty($user->confirmed_date) && !is_numeric($user->confirmed_date)) $user->confirmed_date = strtotime($user->confirmed_date);
		if(!empty($user->lastclick_date) && !is_numeric($user->lastclick_date)) $user->lastclick_date = strtotime($user->lastclick_date);
		if(!empty($user->lastopen_date) && !is_numeric($user->lastopen_date)) $user->lastopen_date = strtotime($user->lastopen_date);

		if(empty($user->name) AND $this->generatename) $user->name = ucwords(trim(str_replace(array('.','_','-',1,2,3,4,5,6,7,8,9,0),' ',substr($user->email,0,strpos($user->email,'@')))));

		if((!isset($user->confirmed) || strlen($user->confirmed) == 0 ) AND $this->forceconfirm) $user->confirmed = 1;

		if(empty($user->key)) $user->key = md5(substr($user->email,0,strpos($user->email,'@')).rand(0,10000000));
	}


	function _autoDetectHeader(){
		$app = JFactory::getApplication();

		$this->separator = ',';

		$this->header = str_replace("\xEF\xBB\xBF","",$this->header);

		$listSeparators = array("\t",';',',');
		foreach($listSeparators as $sep){
			if(strpos($this->header,$sep) !== false){
				$this->separator = $sep;
				break;
			}
		}


		$this->columns = explode($this->separator,$this->header);

		for($i=count($this->columns)-1;$i>=0;$i--){
			if(strlen($this->columns[$i]) == 0){
				unset($this->columns[$i]);
				$this->removeSep++;
			}
		}

		$columns = acymailing_getColumns('#__acymailing_subscriber');

		foreach($this->columns as $i => $oneColumn){
			$this->columns[$i] = strtolower(trim($oneColumn,'\'" '));
			if($this->columns[$i] == 'listids') continue;
			if(!isset($columns[$this->columns[$i]])){
				$app->enqueueMessage(JText::sprintf('IMPORT_ERROR_FIELD','<b><i>'.$this->columns[$i].'</i></b>',implode(' | ',array_diff(array_keys($columns),array('subid','userid','key')))),'error');
				return false;
			}
		}

		if(!in_array('email',$this->columns)) return false;

		return true;

	}

	function joomla(){
		$app = JFactory::getApplication();

		$query = 'UPDATE IGNORE '.acymailing_table('users',false).' as b, '.acymailing_table('subscriber').' as a SET a.email = b.email, a.name = b.name, a.enabled = 1 - b.block WHERE a.userid = b.id AND a.userid > 0';
		$this->db->setQuery($query);
		$this->db->query();
		$nbUpdated = $this->db->getAffectedRows();

		$query = 'UPDATE IGNORE '.acymailing_table('users',false).' as b, '.acymailing_table('subscriber').' as a SET a.userid = b.id WHERE a.email = b.email';
		$this->db->setQuery($query);
		$this->db->query();
		$nbUpdated += $this->db->getAffectedRows();

		$app->enqueueMessage(JText::sprintf('IMPORT_UPDATE',$nbUpdated));

		$query = 'SELECT subid FROM '.acymailing_table('subscriber').' as a LEFT JOIN '.acymailing_table('users',false).' as b on a.userid = b.id WHERE b.id IS NULL AND a.userid > 0';
		$this->db->setQuery($query);
		$deletedSubid = acymailing_loadResultArray($this->db);

		$query = 'SELECT subid FROM '.acymailing_table('subscriber').' as a LEFT JOIN '.acymailing_table('users',false).' as b on a.email = b.email WHERE b.id IS NULL AND a.userid > 0';
		$this->db->setQuery($query);
		$deletedSubid = array_merge(acymailing_loadResultArray($this->db),$deletedSubid);

		if(!empty($deletedSubid)){
			$userClass = acymailing_get('class.subscriber');
			$deletedUsers = $userClass->delete($deletedSubid);
			$app->enqueueMessage(JText::sprintf('IMPORT_DELETE',$deletedUsers));
		}

		$time = time();
		$query = 'INSERT IGNORE INTO '.acymailing_table('subscriber').' (`email`,`name`,`confirmed`,`userid`,`created`,`enabled`,`accept`,`html`) SELECT `email`,`name`,1-`block`,`id`,UNIX_TIMESTAMP(`registerDate`),1-`block`,1,1 FROM '.acymailing_table('users',false);
		$this->db->setQuery($query);
		$this->db->query();
		$insertedUsers = $this->db->getAffectedRows();

		$app->enqueueMessage(JText::sprintf('IMPORT_NEW',$insertedUsers));

		$lists = JRequest::getVar('importlists',array());
		$listsSubscribe = array();
		foreach($lists as $listid => $val){
			if(!empty($val)) $listsSubscribe[] = (int) $listid;
		}

		if(empty($listsSubscribe)) return true;

		$query = 'INSERT IGNORE INTO '.acymailing_table('listsub').' (`listid`,`subid`,`subdate`,`status`) ';
		$query.= 'SELECT a.`listid`, b.`subid` ,'.$time.',1 FROM '.acymailing_table('list').' as a, '.acymailing_table('subscriber').' as b  WHERE a.`listid` IN ('.implode(',',$listsSubscribe).') AND b.`userid` > 0';
		$this->db->setQuery($query);
		$this->db->query();
		$nbsubscribed = $this->db->getAffectedRows();

		$app->enqueueMessage(JText::sprintf('IMPORT_SUBSCRIPTION',$nbsubscribed));

		return true;

	}

	function acajoom(){
		$app = JFactory::getApplication();

		$query = 'INSERT IGNORE INTO '.acymailing_table('subscriber').' (email,name,confirmed,created,enabled,accept,html) SELECT email,name,confirmed,UNIX_TIMESTAMP(`subscribe_date`),1-blacklist,1,receive_html FROM '.acymailing_table('acajoom_subscribers',false);
		$this->db->setQuery($query);
		$this->db->query();
		$insertedUsers = $this->db->getAffectedRows();

		$app->enqueueMessage(JText::sprintf('IMPORT_NEW',$insertedUsers));

		if(JRequest::getInt('acajoom_lists',0) == 1) $this->_importAcajoomLists();

		$query = 'SELECT b.subid FROM '.acymailing_table('acajoom_subscribers',false).' as a JOIN '.acymailing_table('subscriber').' as b on a.email = b.email';
		$this->db->setQuery($query);
		$this->allSubid = acymailing_loadResultArray($this->db);
		$this->_subscribeUsers();
		$this->_displaySubscribedResult();

		return true;
	}

	function _importYancLists(){
		$app = JFactory::getApplication();

		$query = 'SELECT `id`, `name`, `description`, `state` as `published` FROM `#__yanc_letters`';
		$this->db->setQuery($query);
		$yancLists = $this->db->loadObjectList('id');
		$user = JFactory::getUser();

		$query = 'SELECT `listid`, `alias` FROM '.acymailing_table('list').' WHERE `alias` IN (\'yanclist'.implode('\',\'yanclist',array_keys($yancLists)).'\')';
		$this->db->setQuery($query);
		$joomLists = $this->db->loadObjectList('alias');

		$listClass = acymailing_get('class.list');
		$time = time();

		foreach($yancLists as $oneList){
			$oneList->alias = 'yanclist'.$oneList->id;
			$oneList->userid = $user->id;

			$yancListId = $oneList->id;
			if(isset($joomLists[$oneList->alias])){
				$joomListId = $joomLists[$oneList->alias]->listid;
			}else{
				unset($oneList->id);
				$joomListId = $listClass->save($oneList);
				$app->enqueueMessage(JText::sprintf('IMPORT_LIST','<b><i>'.$oneList->name.'</i></b>'));
			}

			$querySelect = 'SELECT DISTINCT c.subid,'.$joomListId.','.$time.',1 FROM `#__yanc_subscribers` as a ';
			$querySelect .= 'JOIN '.acymailing_table('subscriber').' as c on a.email = c.email ';
			$querySelect .= 'WHERE a.lid = '.$yancListId.' AND a.state = 1 AND c.subid > 0';
			$queryInsert = 'INSERT IGNORE INTO '.acymailing_table('listsub').' (subid,listid,subdate,status) ';

			$this->db->setQuery($queryInsert.$querySelect);
			$this->db->query();

			$app->enqueueMessage(JText::sprintf('IMPORT_SUBSCRIBE_CONFIRMATION',$this->db->getAffectedRows(),'<b><i>'.$oneList->name.'</i></b>'));
		}

		return true;

	}

	function _importjnewsLists(){
		$app = JFactory::getApplication();

		$query = 'SELECT `id`, `list_name` as `name`, `hidden` as `visible`, `list_desc` as `description`, `published`, `owner` as `userid` FROM '.acymailing_table('jnews_lists',false);
		$this->db->setQuery($query);
		$jnewsLists = $this->db->loadObjectList('id');

		$query = 'SELECT `listid`, `alias` FROM '.acymailing_table('list').' WHERE `alias` IN (\'jnewslist'.implode('\',\'jnewslist',array_keys($jnewsLists)).'\')';
		$this->db->setQuery($query);
		$joomLists = $this->db->loadObjectList('alias');

		$listClass = acymailing_get('class.list');

		foreach($jnewsLists as $oneList){
			$oneList->alias = 'jnewslist'.$oneList->id;
			$jnewsListId = $oneList->id;
			if(isset($joomLists[$oneList->alias])){
				$joomListId = $joomLists[$oneList->alias]->listid;
			}else{
				unset($oneList->id);
				$joomListId = $listClass->save($oneList);
				$app->enqueueMessage(JText::sprintf('IMPORT_LIST','<b><i>'.$oneList->name.'</i></b>'));
			}

			$querySelect = 'SELECT DISTINCT c.subid,'.$joomListId.',a.subdate,a.unsubdate,1-(2*a.unsubscribe) FROM '.acymailing_table('jnews_listssubscribers',false).' as a ';
			$querySelect .= 'JOIN '.acymailing_table('jnews_subscribers',false).' as b on a.subscriber_id = b.id ';
			$querySelect .= 'JOIN '.acymailing_table('subscriber').' as c on b.email = c.email ';
			$querySelect .= 'WHERE a.list_id = '.$jnewsListId.' AND c.subid > 0';
			$queryInsert = 'INSERT IGNORE INTO '.acymailing_table('listsub').' (subid,listid,subdate,unsubdate,status) ';

			$this->db->setQuery($queryInsert.$querySelect);
			$this->db->query();

			$app->enqueueMessage(JText::sprintf('IMPORT_SUBSCRIBE_CONFIRMATION',$this->db->getAffectedRows(),'<b><i>'.$oneList->name.'</i></b>'));
		}

		return true;

	}

	function _importAcajoomLists(){
		$app = JFactory::getApplication();

		$query = 'SELECT `id`, `list_name` as `name`, `hidden` as `visible`, `list_desc` as `description`, `published`, `owner` as `userid` FROM '.acymailing_table('acajoom_lists',false);
		$this->db->setQuery($query);
		$acaLists = $this->db->loadObjectList('id');

		$query = 'SELECT `listid`, `alias` FROM '.acymailing_table('list').' WHERE `alias` IN (\'acajoomlist'.implode('\',\'acajoomlist',array_keys($acaLists)).'\')';
		$this->db->setQuery($query);
		$joomLists = $this->db->loadObjectList('alias');

		$listClass = acymailing_get('class.list');
		$time = time();

		foreach($acaLists as $oneList){
			$oneList->alias = 'acajoomlist'.$oneList->id;
			$acaListId = $oneList->id;
			if(isset($joomLists[$oneList->alias])){
				$joomListId = $joomLists[$oneList->alias]->listid;
			}else{
				unset($oneList->id);
				$joomListId = $listClass->save($oneList);
				$app->enqueueMessage(JText::sprintf('IMPORT_LIST','<b><i>'.$oneList->name.'</i></b>'));
			}

			$querySelect = 'SELECT DISTINCT c.subid,'.$joomListId.','.$time.',1 FROM '.acymailing_table('acajoom_queue',false).' as a ';
			$querySelect .= 'JOIN '.acymailing_table('acajoom_subscribers',false).' as b on a.subscriber_id = b.id ';
			$querySelect .= 'JOIN '.acymailing_table('subscriber').' as c on b.email = c.email ';
			$querySelect .= 'WHERE a.list_id = '.$acaListId.' AND c.subid > 0';
			$queryInsert = 'INSERT IGNORE INTO '.acymailing_table('listsub').' (subid,listid,subdate,status) ';

			$this->db->setQuery($queryInsert.$querySelect);
			$this->db->query();

			$app->enqueueMessage(JText::sprintf('IMPORT_SUBSCRIBE_CONFIRMATION',$this->db->getAffectedRows(),'<b><i>'.$oneList->name.'</i></b>'));
		}

		return true;

	}

	function letterman(){
		$app = JFactory::getApplication();

		$time = time();
		$query = 'INSERT IGNORE INTO '.acymailing_table('subscriber').' (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`) SELECT `subscriber_email`,`subscriber_name`,`confirmed`,UNIX_TIMESTAMP(`subscribe_date`),1,1,1 FROM '.acymailing_table('letterman_subscribers',false);
		$this->db->setQuery($query);
		$this->db->query();
		$insertedUsers = $this->db->getAffectedRows();

		if($insertedUsers == -1){
			$query = 'INSERT IGNORE INTO '.acymailing_table('subscriber').' (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`) SELECT `email`,`name`,`confirmed`,'.$time.',1,1,1 FROM '.acymailing_table('letterman_subscribers',false);
			$this->db->setQuery($query);
			$this->db->query();
			$insertedUsers = $this->db->getAffectedRows();
			$query = 'SELECT b.subid FROM '.acymailing_table('letterman_subscribers',false).' as a JOIN '.acymailing_table('subscriber').' as b on a.email = b.email';
			$this->db->setQuery($query);
		}else{
			$query = 'SELECT b.subid FROM '.acymailing_table('letterman_subscribers',false).' as a JOIN '.acymailing_table('subscriber').' as b on a.subscriber_email = b.email';
			$this->db->setQuery($query);
		}

		$app->enqueueMessage(JText::sprintf('IMPORT_NEW',$insertedUsers));

		$this->allSubid = acymailing_loadResultArray($this->db);
		$this->_subscribeUsers();
		$this->_displaySubscribedResult();

		return true;
	}

	function yanc(){
		$app = JFactory::getApplication();

		$this->db->setQuery('SELECT * FROM #__yanc_subscribers LIMIT 1');
		$oneSubscriber = $this->db->loadObject();
		if(!isset($oneSubscriber->state)){
			$this->db->setQuery("ALTER IGNORE TABLE `#__yanc_subscribers` ADD `state` INT NOT NULL DEFAULT '1'");
			$this->db->query();
		}

		$query = 'INSERT IGNORE INTO '.acymailing_table('subscriber').' (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`, `ip`) SELECT `email`,`name`,`confirmed`,UNIX_TIMESTAMP(`date`),`state`,1,`html`,`ip` FROM '.acymailing_table('yanc_subscribers',false)." WHERE email LIKE '%@%'";
		$this->db->setQuery($query);
		$this->db->query();
		$insertedUsers = $this->db->getAffectedRows();

		$app->enqueueMessage(JText::sprintf('IMPORT_NEW',$insertedUsers));

		if(JRequest::getInt('yanc_lists',0) == 1) $this->_importYancLists();

		$query = 'SELECT b.subid FROM '.acymailing_table('yanc_subscribers',false).' as a JOIN '.acymailing_table('subscriber').' as b on a.email = b.email';
		$this->db->setQuery($query);
		$this->allSubid = acymailing_loadResultArray($this->db);
		$this->_subscribeUsers();
		$this->_displaySubscribedResult();

		return true;
	}


	function vemod(){
		$app = JFactory::getApplication();
		$time = time();
		$query = "INSERT IGNORE INTO ".acymailing_table('subscriber')." (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`) SELECT `email`,`name`,1,'.$time.',1,1,`mailformat` FROM `#__vemod_news_mailer_users` WHERE `email` LIKE '%@%' ";
		$this->db->setQuery($query);
		$this->db->query();
		$insertedUsers = $this->db->getAffectedRows();

		$app->enqueueMessage(JText::sprintf('IMPORT_NEW',$insertedUsers));

		$query = 'SELECT b.subid FROM `#__vemod_news_mailer_users` as a JOIN '.acymailing_table('subscriber').' as b on a.email = b.email';
		$this->db->setQuery($query);
		$this->allSubid = acymailing_loadResultArray($this->db);
		$this->_subscribeUsers();
		$this->_displaySubscribedResult();

		return true;
	}

	function contact(){
		$app = JFactory::getApplication();
		$time = time();
		$query = 'INSERT IGNORE INTO '.acymailing_table('subscriber')." (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`) SELECT `email_to`,`name`,1,'.$time.',1,1,1 FROM `#__contact_details` WHERE email_to LIKE '%@%'";
		$this->db->setQuery($query);
		$this->db->query();
		$insertedUsers = $this->db->getAffectedRows();

		$app->enqueueMessage(JText::sprintf('IMPORT_NEW',$insertedUsers));

		$query = 'SELECT b.subid FROM `#__contact_details` as a JOIN '.acymailing_table('subscriber').' as b on a.email_to = b.email';
		$this->db->setQuery($query);
		$this->allSubid = acymailing_loadResultArray($this->db);
		$this->_subscribeUsers();
		$this->_displaySubscribedResult();

		return true;
	}

	function ccnewsletter(){
		$app = JFactory::getApplication();

		$query = 'INSERT IGNORE INTO '.acymailing_table('subscriber').' (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`) SELECT `email`,`name`,`enabled`,UNIX_TIMESTAMP(`sdate`),`enabled`,1,1-`plainText` FROM '.acymailing_table('ccnewsletter_subscribers',false);
		$this->db->setQuery($query);
		$this->db->query();
		$insertedUsers = $this->db->getAffectedRows();

		$app->enqueueMessage(JText::sprintf('IMPORT_NEW',$insertedUsers));

		$query = 'SELECT b.subid FROM '.acymailing_table('ccnewsletter_subscribers',false).' as a JOIN '.acymailing_table('subscriber').' as b on a.email = b.email WHERE b.subid > 0';
		$this->db->setQuery($query);
		$this->allSubid = acymailing_loadResultArray($this->db);
		$this->_subscribeUsers();
		$this->_displaySubscribedResult();

		return true;
	}

	function jnews(){
		$app = JFactory::getApplication();

		$time = time();
		$query = 'INSERT IGNORE INTO '.acymailing_table('subscriber').' (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`) SELECT `email`,`name`,`confirmed`,`subscribe_date`, 1-`blacklist`,1,`receive_html` FROM '.acymailing_table('jnews_subscribers',false);
		$this->db->setQuery($query);
		$this->db->query();
		$insertedUsers = $this->db->getAffectedRows();

		$app->enqueueMessage(JText::sprintf('IMPORT_NEW',$insertedUsers));

		if(JRequest::getInt('jnews_lists',0) == 1) $this->_importjnewsLists();

		$query = 'SELECT b.subid FROM '.acymailing_table('jnews_subscribers',false).' as a JOIN '.acymailing_table('subscriber').' as b on a.email = b.email';
		$this->db->setQuery($query);
		$this->allSubid = acymailing_loadResultArray($this->db);
		$this->_subscribeUsers();
		$this->_displaySubscribedResult();

		return true;
	}

	function nspro(){
		$app = JFactory::getApplication();

		$time = time();
		$query = 'INSERT IGNORE INTO '.acymailing_table('subscriber').' (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`) SELECT `email`,`name`,`confirmed`,UNIX_TIMESTAMP(`datetime`), 1,1,1 FROM '.acymailing_table('nspro_subs',false);
		$this->db->setQuery($query);
		$this->db->query();
		$insertedUsers = $this->db->getAffectedRows();

		$app->enqueueMessage(JText::sprintf('IMPORT_NEW',$insertedUsers));

		if(JRequest::getInt('nspro_lists',0) == 1) $this->_importnsproLists();

		$query = 'SELECT b.subid FROM '.acymailing_table('nspro_subs',false).' as a JOIN '.acymailing_table('subscriber').' as b on a.email = b.email';
		$this->db->setQuery($query);
		$this->allSubid = acymailing_loadResultArray($this->db);
		$this->_subscribeUsers();
		$this->_displaySubscribedResult();

		return true;
	}

	function _importnsproLists(){
		$app = JFactory::getApplication();

		$my = JFactory::getUser();

		$query = 'SELECT `id`, `lname` as `name`, 1 as `visible`, `notes` as `description`, `published`, '.intval($my->id).' as `userid` FROM '.acymailing_table('nspro_lists',false);
		$this->db->setQuery($query);
		$nsprolists = $this->db->loadObjectList('id');

		$query = 'SELECT `listid`, `alias` FROM '.acymailing_table('list').' WHERE `alias` IN (\'nsprolist'.implode('\',\'nsprolist',array_keys($nsprolists)).'\')';
		$this->db->setQuery($query);
		$joomLists = $this->db->loadObjectList('alias');

		$listClass = acymailing_get('class.list');

		foreach($nsprolists as $oneList){
			$oneList->alias = 'nsprolist'.$oneList->id;
			$nsproListId = $oneList->id;
			if(isset($joomLists[$oneList->alias])){
				$joomListId = $joomLists[$oneList->alias]->listid;
			}else{
				unset($oneList->id);
				$joomListId = $listClass->save($oneList);
				$app->enqueueMessage(JText::sprintf('IMPORT_LIST','<b><i>'.$oneList->name.'</i></b>'));
			}

			$querySelect = 'SELECT DISTINCT c.subid,'.$joomListId.',c.created,1 FROM '.acymailing_table('nspro_subs',false).' as a ';
			$querySelect .= 'JOIN '.acymailing_table('subscriber').' as c on a.email = c.email ';
			$querySelect .= 'WHERE a.mailing_lists LIKE "'.$nsproListId.'" OR a.mailing_lists LIKE "%,'.$nsproListId.',%" OR a.mailing_lists LIKE "'.$nsproListId.',%"  OR a.mailing_lists LIKE "%,'.$nsproListId.'"';
			$queryInsert = 'INSERT IGNORE INTO '.acymailing_table('listsub').' (subid,listid,subdate,status) ';

			$this->db->setQuery($queryInsert.$querySelect);
			$this->db->query();

			$app->enqueueMessage(JText::sprintf('IMPORT_SUBSCRIBE_CONFIRMATION',$this->db->getAffectedRows(),'<b><i>'.$oneList->name.'</i></b>'));
		}

		return true;
	}

	function communicator(){
		$app = JFactory::getApplication();

		$time = time();
		$query = 'INSERT IGNORE INTO '.acymailing_table('subscriber').' (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`) SELECT `subscriber_email`,`subscriber_name`,`confirmed`,'.$time.',1,1,1 FROM '.acymailing_table('communicator_subscribers',false);
		$this->db->setQuery($query);
		$this->db->query();
		$insertedUsers = $this->db->getAffectedRows();

		$app->enqueueMessage(JText::sprintf('IMPORT_NEW',$insertedUsers));

		$query = 'SELECT b.subid FROM '.acymailing_table('communicator_subscribers',false).' as a JOIN '.acymailing_table('subscriber').' as b on a.subscriber_email = b.email';
		$this->db->setQuery($query);
		$this->allSubid = acymailing_loadResultArray($this->db);
		$this->_subscribeUsers();
		$this->_displaySubscribedResult();

		return true;
	}

	function civi_import(){
		$query = 'INSERT IGNORE INTO '.acymailing_table('subscriber').' (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`) ';
		$query .= 'SELECT CONVERT(civiemail.email USING utf8),CONVERT(civicontact.`first_name` USING utf8),1,'.time().', 1-`do_not_email`,1 - civicontact.is_opt_out,1 ';
		$query .= 'FROM `civicrm_email` as civiemail JOIN civicrm_contact as civicontact ON civicontact.id = civiemail.contact_id ';
		$query .= 'WHERE civicontact.is_deleted = 0 AND civiemail.is_primary = 1 AND civiemail.email LIKE \'%@%\'';
		$this->db->setQuery($query);
		$this->db->query();
		return $this->db->getAffectedRows();
	}

	function civi(){
		$app = JFactory::getApplication();
		$insertedUsers = $this->civi_import();
		$app->enqueueMessage(JText::sprintf('IMPORT_NEW',$insertedUsers));

		$query = 'SELECT b.subid FROM civicrm_email as a JOIN '.acymailing_table('subscriber').' as b on CONVERT(a.email USING utf8) = b.email';
		$this->db->setQuery($query);
		$this->allSubid = acymailing_loadResultArray($this->db);
		$this->_subscribeUsers();
		$this->_displaySubscribedResult();
	}

	function ldap(){

		$config = acymailing_config();
		$app = JFactory::getApplication();

		$db = JFactory::getDBO();
		$db->setQuery("DELETE FROM #__acymailing_config WHERE namekey LIKE 'ldapfield_%'");
		$db->query();

		if(!$this->ldap_init()) return false;

		$ldapfields = JRequest::getVar('ldapfield');
		if(empty($ldapfields)){
			$app->enqueueMessage(JText::_('SPECIFYFIELDEMAIL'),'notice');
			return false;
		}

		$newConfig = new stdClass();

		$this->dispresults = false;
		$newConfig->ldap_import_confirm = $this->forceconfirm = JRequest::getInt('ldap_import_confirm');
		$newConfig->ldap_generatename = $this->generatename = JRequest::getInt('ldap_generatename');
		$newConfig->ldap_overwriteexisting = $this->overwrite = JRequest::getInt('ldap_overwriteexisting');
		$newConfig->ldap_deletenotexists = $this->ldap_deletenotexists = JRequest::getInt('ldap_deletenotexists');
		if($this->ldap_deletenotexists){
			$subfields = array_keys(acymailing_getColumns('#__acymailing_subscriber'));
			if(!in_array('ldapentry',$subfields)){
				$db->setQuery("ALTER TABLE #__acymailing_subscriber ADD COLUMN ldapentry TINYINT UNSIGNED DEFAULT 0");
				$db->query();
			}else{
				$db->setQuery("UPDATE #__acymailing_subscriber SET ldapentry = 0");
				$db->query();
			}

			$this->overwrite = 1;
		}
		$newConfig->ldap_subfield = $this->ldap_subfield = JRequest::getString('ldap_subfield');
		if(!empty($this->ldap_subfield)){
			$allValues = JRequest::getVar('ldap_subcond');
			$allLists = JRequest::getVar('ldap_sublists');
			$this->ldap_subscribe = array();
			foreach($allValues as $i => $oneValue){
				$oneValue = strtolower(trim($oneValue));
				if(strlen($oneValue) <1) continue;
				if(isset($this->ldap_subscribe[$oneValue])){
					$this->ldap_subscribe[$oneValue] .= '-'.intval($allLists[$i]);
				}else{
					$this->ldap_subscribe[$oneValue] = intval($allLists[$i]);
				}
				$valcond = 'ldap_subcond_'.$i;
				$vallist = 'ldap_sublists_'.$i;
				$newConfig->$valcond = $allValues[$i];
				$newConfig->$vallist = $allLists[$i];
			}

			$db->setQuery("DELETE FROM #__acymailing_config WHERE namekey LIKE 'ldap_subcond%' OR namekey LIKE 'ldap_sublists%'");
			$db->query();
		}

		$this->ldap_equivalent = array();
		$this->ldap_selectedFields = array();
		foreach($ldapfields as $oneField => $acyField){
			if(empty($acyField)) continue;
			$configname = 'ldapfield_'.strtolower($oneField);
			$newConfig->$configname = $acyField;
			$this->ldap_equivalent[$acyField] = $oneField;
			$this->ldap_selectedFields[] = $oneField;
		}

		if(!empty($this->ldap_subfield) AND !in_array($this->ldap_subfield,$this->ldap_selectedFields)){
			$this->ldap_selectedFields[] = $this->ldap_subfield;
		}

		$config->save($newConfig);

		if(empty($this->ldap_equivalent['email'])){
			$app->enqueueMessage(JText::_('SPECIFYFIELDEMAIL'),'notice');
			return false;
		}

		$startChars = 'abcdefghijklmnopqrstuvwxyz0123456789_-+&.';

		$nbChars = strlen($startChars);
		$result = true;
		for($i = 0;$i<$nbChars;$i++){
			if(!$this->ldap_import($this->ldap_equivalent['email'].'='.$startChars[$i].'*@*')) $result = false;
		}

		$app->enqueueMessage(JText::sprintf('IMPORT_REPORT',$this->totalTry,$this->totalInserted,$this->totalTry - $this->totalValid,$this->totalValid - $this->totalInserted));

		if($this->ldap_deletenotexists){
			$db->setQuery("SELECT subid FROM #__acymailing_subscriber WHERE ldapentry = 0");
			$allSubids = acymailing_loadResultArray($db);
			$subscriberClass= acymailing_get('class.subscriber');
			$nbAffected = $subscriberClass->delete($allSubids);
			$app->enqueueMessage(JText::sprintf('IMPORT_DELETE',$nbAffected));
			$db->setQuery("ALTER TABLE #__acymailing_subscriber DROP COLUMN ldapentry");
			$db->query();
		}

		$this->_displaySubscribedResult();

		return $result;
	}

	function ldap_import($search){
		$searchResult = ldap_search($this->ldap_conn,$this->ldap_basedn,$search,$this->ldap_selectedFields);
		$app = JFactory::getApplication();
		if(!$searchResult){
			acymailing_display('Could not search for elements<br/>'.ldap_error($this->ldap_conn),'warning');
			return false;
		}
		$entries = ldap_get_entries($this->ldap_conn,$searchResult);

		if(empty($entries) || empty($entries['count'])) return true;

		$content = '"'.implode('","',array_keys($this->ldap_equivalent)).'"';
		if($this->ldap_deletenotexists) $content .= ',"ldapentry"';
		if(!empty($this->ldap_subfield)) $content .= ',"listids"';
		$content .= "\n";
		for($i=0;$i<$entries['count'];$i++){
			foreach($this->ldap_equivalent as $ldapField){
				$fieldVal = isset($entries[$i][$ldapField][0]) ? $entries[$i][$ldapField][0] : '';
				$content .= '"'.$fieldVal.'",';
			}
			if($this->ldap_deletenotexists) $content .= '"1",';
			if(!empty($this->ldap_subfield)){
				static $errorsLists = array();
				if(isset($entries[$i][$this->ldap_subfield][0])){
					$condvalue = strtolower(trim($entries[$i][$this->ldap_subfield][0]));
					if(isset($this->ldap_subscribe[$condvalue])){
						$content .= $this->ldap_subscribe[$condvalue].',';
					}else{
						if(!isset($errorsLists[$condvalue]) AND count($errorsLists) < 5){
							$errorsLists[$condvalue] = true;
							$app->enqueueMessage('Could not find a list for the value "'.$condvalue.'" of the field '.$this->ldap_subfield,'notice');
						}
						$content .= '"",';
					}
				}else{
						$content .= '"",';
				}
			}
			$content = rtrim($content,',');
			$content .= "\n";
		}
		return $this->_handleContent($content);
	}


	function ldap_init(){
		$config = acymailing_config();
		$newConfig = new stdClass();
		$newConfig->ldap_host = trim(JRequest::getString('ldap_host'));
		$newConfig->ldap_port = JRequest::getInt('ldap_port');
		if(empty($newConfig->ldap_port)) $newConfig->ldap_port = 389;
		$newConfig->ldap_basedn = trim(JRequest::getString('ldap_basedn'));
		$this->ldap_basedn = $newConfig->ldap_basedn;
		$newConfig->ldap_username = trim(JRequest::getString('ldap_username'));
		$newConfig->ldap_password = trim(JRequest::getString('ldap_password'));

		$config->save($newConfig);

		if(empty($newConfig->ldap_host)) return false;

		acymailing_displayErrors();
		$this->ldap_conn = ldap_connect($newConfig->ldap_host, $newConfig->ldap_port);
		if(!$this->ldap_conn){
			acymailing_display('Could not connect to LDAP server : '.$newConfig->ldap_host.':'.$newConfig->ldap_port,'warning');
			return false;
		}

		ldap_set_option($this->ldap_conn, LDAP_OPT_PROTOCOL_VERSION,3);
		ldap_set_option($this->ldap_conn, LDAP_OPT_REFERRALS,0);

		if(empty($newConfig->ldap_username)){
			$bindResult = ldap_bind($this->ldap_conn);
		}else{
			$bindResult = ldap_bind($this->ldap_conn,$newConfig->ldap_username,$newConfig->ldap_password);
		}

		if(!$bindResult){
			acymailing_display('Could not bind to the LDAP directory '.$newConfig->ldap_host.':'.$newConfig->ldap_port.' with specified username and password<br/>'.ldap_error($this->ldap_conn),'warning');
			return false;
		}

		acymailing_display('Successfully connected to '.$newConfig->ldap_host.':'.$newConfig->ldap_port,'success');

		return true;
	}

	function ldap_ajax(){

		if(!$this->ldap_init()) return;

		$config = acymailing_config();

		$searchResult = @ldap_search($this->ldap_conn,trim(JRequest::getString('ldap_basedn')),'mail=*@*',array(),0,5);
		if(!$searchResult){
			acymailing_display('Could not search for elements<br/>'.ldap_error($this->ldap_conn),'warning');
			return false;
		}
		$entries = ldap_get_entries($this->ldap_conn,$searchResult);

		$fields = array();
		$dropdown = array();
		$object = new stdClass();
		$object->text = ' - - - ';
		$object->value = 0;
		$dropdown[] = $object;
		foreach($entries as $oneEntry){
			if(!is_array($oneEntry)) continue;
			foreach($oneEntry as $field => $value){
				if(!is_numeric($field)) continue;
				$value = strtolower($value);
				if($value == 'objectclass') continue;
				$fields[$value] = $value;
				$object = new stdClass();
				$object->text = $value;
				$object->value = $value;
				$dropdown[$value] = $object;
			}
		}

		if(empty($fields)){
			acymailing_display('Could not load elements<br/>'.ldap_error($this->ldap_conn),'warning');
			return false;
		}

		$db = JFactory::getDBO();
		$subfields = acymailing_getColumns('#__acymailing_subscriber');

		$acyfields = array();
		$acyfields[] = JHTML::_('select.option', '',' - - - ');
		foreach($subfields as $oneField => $typefield){
			if(in_array($oneField,array('subid','confirmed','enabled','key','userid','accept','html','created'))) continue;
			$acyfields[] = JHTML::_('select.option', $oneField,$oneField);
		}

		echo '<table class="admintable" cellspacing="1">';
		foreach($fields as $oneField){
			echo '<tr><td class="key" >'.$oneField.'</td><td>'.JHTML::_('select.genericlist', $acyfields, 'ldapfield['.$oneField.']' , 'size="1"', 'value', 'text',$config->get('ldapfield_'.$oneField)).'</td></tr>';
		}
		echo '</table>';

		echo '<fieldset><legend>'.JText::_('SUBSCRIPTION').'</legend>';
		echo 'Subscribe the user based on the values of the field '.JHTML::_('select.genericlist',$dropdown,'ldap_subfield', 'size="1"', 'value', 'text',$config->get('ldap_subfield')).':';
		$listClass = acymailing_get('class.list');
		$lists = $listClass->getLists('listid');

		for($i=0;$i<5;$i++){
			echo '<br/>Subscribe to list '.JHTML::_('select.genericlist',  $lists, 'ldap_sublists['.$i.']', 'class="inputbox" size="1" ', 'listid', 'name', (int) $config->get('ldap_sublists_'.$i)).' if the value is <input type="text" value="'.htmlspecialchars($config->get('ldap_subcond_'.$i),ENT_COMPAT, 'UTF-8').'" name="ldap_subcond['.$i.']" />';
		}
		echo '</fieldset>';

	}

	function zohocrm($action = ''){
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$zohoHelper = acymailing_get('helper.zoho');
		$listClass= acymailing_get('class.list');
		$subscriberClass = acymailing_get('class.subscriber');
		$tableInfos = array_keys(acymailing_getColumns('#__acymailing_subscriber'));
		$config =& acymailing_config();
		if(!in_array('zohoid',$tableInfos)){
			$query = 'ALTER TABLE #__acymailing_subscriber ADD COLUMN zohoid VARCHAR(255)';
			$db->setQuery($query);
			$db->query();
		}
		if(!in_array('zoholist',$tableInfos)){
			$query = 'ALTER TABLE #__acymailing_subscriber ADD COLUMN zoholist CHAR(1)';
			$db->setQuery($query);
			$db->query();
		}

		if($action == 'update'){
			$lists = unserialize($config->get('zoho_lists'));
			$zohoHelper->authtoken = $authtoken = $config->get('zoho_apikey');
			$fields = unserialize($config->get('zoho_fields'));
			$confirmedUsers = $config->get('zoho_confirmed');
			$delete = $config->get('zoho_delete');
			$importlists = array();

		}else{
			$lists = JRequest::getVar('zoho_lists');
			$fields = JRequest::getVar('zoho_fields');
			$zohoHelper->authtoken = $authtoken = JRequest::getVar('zoho_apikey');
			$importlists = JRequest::getVar('importlists');
			$overwrite = JRequest::getVar('zoho_overwrite');
			$confirmedUsers = JRequest::getVar('zoho_confirmed');
			$delete = JRequest::getVar('zoho_delete');
			$newConfig = new stdClass();
			$newConfig->zoho_fields = serialize($fields);
			$newConfig->zoho_lists = serialize($lists);
			$newConfig->zoho_apikey = $zohoHelper->authtoken;
			$newConfig->zoho_overwrite = $overwrite;
			$newConfig->zoho_confirmed = $confirmedUsers;
			$newConfig->zoho_delete = $delete;
			$config->save($newConfig);
		}
		if($config->get('zoho_overwrite',false)) $this->overwrite = true;
		if(empty($authtoken)){
			$app->enqueueMessage('Pleaser enter a valid API key','notice');
			return false;
		}
		$done = false;
		foreach($lists as $list => $listid){
			if(empty($listid)) continue;
			$done = true;
			$this->allSubid = array();
			$res = $zohoHelper->sendInfo($list);
			if(!empty($zohoHelper->conn))	$zohoHelper->close();
			if($res != false)	$zohoUsers = $zohoHelper->parseXML($res,$list,$fields,$confirmedUsers);
			if (empty($zohoUsers)) continue;
			$this->_insertUsers($zohoUsers);
			$affectedUsers = $zohoHelper->subscribe($listid, $list);
			if(empty($this->subscribedUsers[$listid])) $this->subscribedUsers[$listid] = $listClass->get($listid);
			$this->subscribedUsers[$listid]->nbusers += $affectedUsers;
			foreach($importlists as $importlistid => $selected)
				if($selected == '1') $importUserInLists[] = $importlistid;
			$this->_subscribeUsers();
			if(JRequest::getInt('zoho_delete') == '1'){
				$zohoHelper->deleteAddress($this->allSubid,$list);
			}else{
				$query = 'SELECT DISTINCT b.subid FROM #__acymailing_subscriber AS a JOIN #__acymailing_subscriber AS b ON a.zohoid = b.zohoid WHERE a.zohoid IS NOT NULL AND b.subid < a.subid';
				$db->setQuery($query);
				$result = acymailing_loadResultArray($db);
				$subscriberClass->delete($result);
			}
		}
		$this->_displaySubscribedResult();
		if($done == false) $app->enqueueMessage('Please select at least one list to import your Zoho users','notice');
		if(!empty($zohoHelper->error)) $app->enqueueMessage(JText::sprintf($zohoHelper->error),'notice');
	}

	function sobipro(){
		$config = acymailing_config();
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$sobiproImport = JRequest::getVar('config',array(),'POST','array');
		$newConfig = new stdClass();
		$affectedRows = 0;
		$newConfig->sobipro_import = serialize($sobiproImport);
		$config->save($newConfig);

		foreach($sobiproImport as $oneImport => $oneValue){
			$query='SELECT fid, nid FROM #__sobipro_field WHERE fid="'.$oneValue['sobiEmail'].'" OR fid="'.$oneValue['sobiName'].'"';
			$db->setQuery($query);
			$nidResult = $db->loadObjectList("fid");
			if(empty($nidResult[$oneValue['sobiEmail']]) OR empty($nidResult[$oneValue['sobiName']])) continue;
			$time = time();
			$query = 'INSERT IGNORE INTO '.acymailing_table('subscriber').' (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`) SELECT b.baseData AS email, a.baseData AS name, 1 as confirmed, '.$time.' as created, 1 as enabled, 1 as accept, 1 as html FROM #__sobipro_field_data AS a LEFT JOIN #__sobipro_field_data AS b ON a.sid=b.sid WHERE a.`fid` = '.$nidResult[$oneValue["sobiName"]]->fid.' AND b.`fid` = '.$nidResult[$oneValue["sobiEmail"]]->fid.' AND b.baseData LIKE "%@%" AND b.baseData IS NOT NULL AND a.baseData IS NOT NULL ORDER by a.sid ';
			$db->setQuery($query);
			$db->query();
			$affectedRows += $db->getAffectedRows();
		}
		$app->enqueueMessage(JText::sprintf('IMPORT_NEW',$affectedRows));
		$query = 'SELECT b.subid FROM `#__sobipro_field_data` as a JOIN '.acymailing_table('subscriber').' as b on a.baseData = b.email';
		$this->db->setQuery($query);
		$this->allSubid = acymailing_loadResultArray($this->db);
		$this->_subscribeUsers();
		$this->_displaySubscribedResult();
		return true;
	}
}
