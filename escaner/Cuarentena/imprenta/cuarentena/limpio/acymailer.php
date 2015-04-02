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

require_once(ACYMAILING_FRONT.'inc'.DS.'phpmailer'.DS.'class.phpmailer.php');

class acymailerHelper extends acymailingPHPMailer {

	var $report = true;

	var $loadedToSend = true;

	var $checkConfirmField = true;

	var $checkEnabled = true;

	var $checkAccept = true;

	var $parameters = array();

	var $dispatcher;

	var $errorNumber = 0;

	var $reportMessage = '';

	var $autoAddUser = false;

	var $errorNewTry = array(1,6);

	var $app;

	var $forceTemplate = 0;

	var $alreadyCheckedAddresses = false;

	var $checkPublished = true;

	var $introtext;


	function acymailerHelper() {

		static $loaded = false;
		if(!$loaded){
			$loaded = true;
			JPluginHelper::importPlugin('acymailing');
		}

		$this->dispatcher = JDispatcher::getInstance();

		$this->app = JFactory::getApplication();

		$this->subscriberClass = acymailing_get('class.subscriber');
		$this->encodingHelper = acymailing_get('helper.encoding');
		$this->userHelper = acymailing_get('helper.user');


		$this->config =& acymailing_config();
		$this->setFrom($this->config->get('from_email'),$this->config->get('from_name'));

		$this->Sender 	= $this->cleanText($this->config->get('bounce_email'));
		if(empty($this->Sender)) $this->Sender = '';

		switch ( $this->config->get('mailer_method','phpmail') )
		{
			case 'smtp' :
				$this->IsSMTP();
				$this->Host = trim($this->config->get('smtp_host'));
				$port = $this->config->get('smtp_port');
				if(empty($port) && $this->config->get('smtp_secured') == 'ssl') $port = 465;
				if(!empty($port)) $this->Host.= ':'.$port;
				$this->SMTPAuth = (bool) $this->config->get('smtp_auth',true);
				$this->Username = trim($this->config->get('smtp_username'));
				$this->Password = trim($this->config->get('smtp_password'));
				$this->SMTPSecure = trim((string)$this->config->get('smtp_secured'));

				if(empty($this->Sender)) $this->Sender = strpos($this->Username,'@') ? $this->Username : $this->config->get('from_email');
				break;
			case 'sendmail' :
				$this->IsSendmail();
				$this->SendMail = trim($this->config->get('sendmail_path'));
				if(empty($this->SendMail)) $this->SendMail = '/usr/sbin/sendmail';
				break;
			case 'qmail' :
				$this->IsQmail();
				break;
			case 'elasticemail' :
				$port = $this->config->get('elasticemail_port','rest');
				if(is_numeric($port)){
					$this->IsSMTP();
					if($port == '25'){
						$this->Host = 'smtp25.elasticemail.com:25';
					}else{
						$this->Host = 'smtp.elasticemail.com:2525';
					}
					$this->Username = trim($this->config->get('elasticemail_username'));
					$this->Password = trim($this->config->get('elasticemail_password'));
					$this->SMTPAuth = true;
				}else{
					include_once (ACYMAILING_FRONT . 'inc' . DS . 'phpmailer' . DS . 'class.elasticemail.php');
					$this->Mailer = 'elasticemail';
					$this->elasticEmail = new acymailingElasticemail();
					$this->elasticEmail->Username = trim($this->config->get('elasticemail_username'));
					$this->elasticEmail->Password = trim($this->config->get('elasticemail_password'));
				}

				break;
			case 'smtp_com' :
				$this->IsSMTP();
				$this->Host = 'retail.smtp.com:2525';
				$this->Username = trim($this->config->get('smtp_com_username'));
				$this->Password = trim($this->config->get('smtp_com_password'));
				$this->SMTPAuth = true;
				break;
			default :
				$this->IsMail();
				break;
		}//endswitch


		$this->PluginDir =  dirname(__FILE__).DS;
		$this->CharSet = strtolower($this->config->get('charset'));
		if(empty($this->CharSet)) $this->CharSet = 'utf-8';

		$this->clearAll();

		$this->Encoding = $this->config->get('encoding_format');
		if(empty($this->Encoding)) $this->Encoding = '8bit';

		$this->Hostname = trim($this->config->get('hostname',''));

		$this->WordWrap = intval($this->config->get('word_wrapping',0));

		@ini_set('pcre.backtrack_limit', 1000000);

	}//endfct

	function send(){

		if(empty($this->ReplyTo)){
			$this->_addReplyTo($this->config->get('reply_email'),$this->config->get('reply_name'));
		}

		if((bool)$this->config->get('embed_images',0) && $this->Mailer != 'elasticemail'){
			$this->embedImages();
		}

		if(empty($this->Subject) OR empty($this->Body)){
			$this->reportMessage = JText::_( 'SEND_EMPTY');
			$this->errorNumber = 8;
			if($this->report){
				$this->app->enqueueMessage($this->reportMessage, 'error');
			}
			return false;
		}

		if(!$this->alreadyCheckedAddresses){
			$this->alreadyCheckedAddresses = true;
			if(empty($this->ReplyTo[0][0]) || !$this->userHelper->validEmail($this->ReplyTo[0][0])){
				$this->reportMessage = JText::_( 'VALID_EMAIL').' ( '.JText::_('REPLYTO_ADDRESS').' : '.$this->ReplyTo[0][0].' ) ';
				$this->errorNumber = 9;
				if($this->report){
					$this->app->enqueueMessage($this->reportMessage, 'error');
				}
				return false;
			}

			if(empty($this->From) || !$this->userHelper->validEmail($this->From)){
				$this->reportMessage = JText::_( 'VALID_EMAIL').' ( '.JText::_('FROM_ADDRESS').' : '.$this->From.' ) ';
				$this->errorNumber = 9;
				if($this->report){
					$this->app->enqueueMessage($this->reportMessage, 'error');
				}
				return false;
			}

			if(!empty($this->Sender) && !$this->userHelper->validEmail($this->Sender)){
				$this->reportMessage = JText::_( 'VALID_EMAIL').' ( '.JText::_('BOUNCE_ADDRESS').' : '.$this->Sender.' ) ';
				$this->errorNumber = 9;
				if($this->report){
					$this->app->enqueueMessage($this->reportMessage, 'error');
				}
				return false;
			}
		}

		if(function_exists('mb_convert_encoding') && !empty($this->sendHTML)){
			$this->Body = mb_convert_encoding($this->Body,'HTML-ENTITIES','UTF-8');
			$this->Body = str_replace(array('&amp;','&sigmaf;'),array('&','ς'),$this->Body);
		}

		if($this->CharSet != 'utf-8'){
			$this->Body = $this->encodingHelper->change($this->Body,'UTF-8',$this->CharSet);
			$this->Subject = $this->encodingHelper->change($this->Subject,'UTF-8',$this->CharSet);
			if(!empty($this->AltBody)) $this->AltBody = $this->encodingHelper->change($this->AltBody,'UTF-8',$this->CharSet);
		}

		if(strpos($this->Host,'elasticemail')){
			$this->addCustomHeader('referral:2f0447bb-173a-459d-ab1a-ab8cbebb9aab');
		}

		$this->Subject = str_replace(array('’','“','”','–'),array("'",'"','"','-'),$this->Subject);

		$this->Body = str_replace(" ",' ',$this->Body);

		ob_start();
		$result = parent::Send();
		$warnings = ob_get_clean();

		if(!empty($warnings) && strpos($warnings,'bloque')){
			$result = false;
		}

		$receivers =  array();
		foreach($this->to as $oneReceiver){
			$receivers[] = $oneReceiver[0];
		}
		if(!$result){
			$this->reportMessage = JText::sprintf( 'SEND_ERROR','<b><i>'.$this->Subject.'</i></b>','<b><i>'.implode(' , ',$receivers).'</i></b>');
			if(!empty($this->ErrorInfo)) $this->reportMessage .= ' | '.$this->ErrorInfo;
			if(!empty($warnings)) $this->reportMessage .= ' | '.$warnings;
			$this->errorNumber = 1;
			if($this->report){
				$this->app->enqueueMessage(nl2br($this->reportMessage), 'error');
			}
		}else{
			$this->reportMessage = JText::sprintf( 'SEND_SUCCESS','<b><i>'.$this->Subject.'</i></b>','<b><i>'.implode(' , ',$receivers).'</i></b>');
			if(!empty($warnings)) $this->reportMessage .= ' | '.$warnings;
			if($this->report){
				$this->app->enqueueMessage(nl2br($this->reportMessage), 'message');
			}
		}

		return $result;
	}

	function load($mailid){
		$mailClass = acymailing_get('class.mail');
		$this->defaultMail[$mailid] = $mailClass->get($mailid);

		if(empty($this->defaultMail[$mailid]->mailid)) return false;

		if(empty($this->defaultMail[$mailid]->altbody)) $this->defaultMail[$mailid]->altbody = $this->textVersion($this->defaultMail[$mailid]->body);

		if(!empty($this->defaultMail[$mailid]->attach)){
			$this->defaultMail[$mailid]->attachments = array();
			$uploadFolder = str_replace(array('/','\\'),DS,html_entity_decode($this->config->get('uploadfolder')));
			$uploadFolder = trim($uploadFolder,DS.' ').DS;
			$uploadPath = str_replace(array('/','\\'),DS,ACYMAILING_ROOT.$uploadFolder);

			$uploadURL = ACYMAILING_LIVE.str_replace(DS,'/',$uploadFolder);

			foreach($this->defaultMail[$mailid]->attach as $oneAttach){
				$attach = new stdClass();
				$attach->name = $oneAttach->filename;
				$attach->filename = $uploadPath.$oneAttach->filename;
				$attach->url = $uploadURL.$oneAttach->filename;
				$this->defaultMail[$mailid]->attachments[] = $attach;
			}
		}

		if(!empty($this->defaultMail[$mailid]->tempid)){
			$templateClass = acymailing_get('class.template');
			$this->defaultMail[$mailid]->template = $templateClass->get($this->defaultMail[$mailid]->tempid);
		}

		$this->dispatcher->trigger('acymailing_replacetags',array(&$this->defaultMail[$mailid],$this->loadedToSend));

		$this->defaultMail[$mailid]->body = acymailing_absoluteURL($this->defaultMail[$mailid]->body);

		return $this->defaultMail[$mailid];
	}

	function clearAll(){
		$this->Subject = '';
		$this->Body = '';
		$this->AltBody = '';
		$this->ClearAllRecipients();
		$this->ClearAttachments();
		$this->ClearCustomHeaders();
		$this->ClearReplyTos();
		$this->errorNumber = 0;
		$this->MessageID = '';

		$this->setFrom($this->config->get('from_email'),$this->config->get('from_name'));


	}

	function sendOne($mailid,$receiverid){
		$this->clearAll();

		if(!isset($this->defaultMail[$mailid])){
			$this->loadedToSend = true;
			if(!$this->load($mailid)){
				$this->reportMessage = 'Can not load the e-mail : '.$mailid;
				if($this->report){
					$this->app->enqueueMessage($this->reportMessage, 'error');
				}
				$this->errorNumber = 2;
				return false;
			}
		}

		if(!empty($this->forceTemplate) AND empty($this->defaultMail[$mailid]->tempid)){
			$this->defaultMail[$mailid]->tempid = $this->forceTemplate;
		}


		if(!isset($this->forceVersion) AND $this->checkPublished AND empty($this->defaultMail[$mailid]->published)){
			$this->reportMessage = JText::sprintf('SEND_ERROR_PUBLISHED',$mailid);
			$this->errorNumber = 3;
			if($this->report){
				$this->app->enqueueMessage($this->reportMessage, 'error');
			}
			return false;
		}

		if(!is_object($receiverid)){
			$receiver = $this->subscriberClass->get($receiverid);
			if(empty($receiver->subid) AND is_string($receiverid) AND $this->autoAddUser){
				if($this->userHelper->validEmail($receiverid)){
					$newUser = new stdClass();
					$newUser->email = $receiverid;
					$this->subscriberClass->checkVisitor = false;
					$this->subscriberClass->sendConf = false;
					$subid = $this->subscriberClass->save($newUser);
					$receiver = $this->subscriberClass->get($subid);
				}
			}
		}else{
			$receiver = $receiverid;
		}

		if(empty($receiver->email)){
			$this->reportMessage = JText::sprintf( 'SEND_ERROR_USER','<b><i>'.(isset($receiver->subid) ? $receiver->subid : $receiverid).'</i></b>');
			if($this->report){
				$this->app->enqueueMessage($this->reportMessage, 'error');
			}
			$this->errorNumber = 4;
			return false;
		}


		$this->MessageID = "<".preg_replace("|[^a-z0-9+_]|i",'',base64_encode(rand(0,9999999))."AC".$receiver->subid."Y".$this->defaultMail[$mailid]->mailid."BA".base64_encode(time().rand(0,99999)))."@".$this->ServerHostname().">";

		if(!isset($this->forceVersion)){
			if($this->checkConfirmField AND empty($receiver->confirmed) AND $this->config->get('require_confirmation',0) AND strpos($this->defaultMail[$mailid]->alias,'confirm') === false){
				$this->reportMessage = JText::sprintf( 'SEND_ERROR_CONFIRMED','<b><i>'.$receiver->email.'</i></b>');
				if($this->report){
					$this->app->enqueueMessage($this->reportMessage, 'error');
				}
				$this->errorNumber = 5;
				return false;
			}

			if($this->checkEnabled AND empty($receiver->enabled) AND strpos($this->defaultMail[$mailid]->alias,'enable') === false){
				$this->reportMessage = JText::sprintf( 'SEND_ERROR_APPROVED','<b><i>'.$receiver->email.'</i></b>');
				if($this->report){
					$this->app->enqueueMessage($this->reportMessage, 'error');
				}
				$this->errorNumber = 6;
				return false;
			}
		}


		if($this->checkAccept AND empty($receiver->accept)){
			$this->reportMessage = JText::sprintf( 'SEND_ERROR_ACCEPT','<b><i>'.$receiver->email.'</i></b>');
			if($this->report){
				$this->app->enqueueMessage($this->reportMessage, 'error');
			}
			$this->errorNumber = 7;
			return false;
		}

		$addedName = $this->config->get('add_names',true) ? $this->cleanText($receiver->name) : '';
		$this->AddAddress($this->cleanText($receiver->email),$addedName);

		if(!isset($this->forceVersion)){
			$this->sendHTML = $receiver->html && $this->defaultMail[$mailid]->html;
			$this->IsHTML($this->sendHTML);
		}else{
			$this->sendHTML = (bool) $this->forceVersion;
			$this->IsHTML($this->sendHTML);
		}

		$this->Subject = $this->defaultMail[$mailid]->subject;

		if($this->sendHTML){
			$this->Body =  $this->defaultMail[$mailid]->body;
			if($this->config->get('multiple_part',false)){
				$this->AltBody = $this->defaultMail[$mailid]->altbody;
			}
		}else{
			$this->Body =  $this->defaultMail[$mailid]->altbody;
		}

		$this->setFrom($this->defaultMail[$mailid]->fromemail,$this->defaultMail[$mailid]->fromname);
		$this->_addReplyTo($this->defaultMail[$mailid]->replyemail,$this->defaultMail[$mailid]->replyname);

		if(!empty($this->defaultMail[$mailid]->attachments)){
			if($this->config->get('embed_files')){
				foreach($this->defaultMail[$mailid]->attachments as $attachment){
					$this->AddAttachment($attachment->filename);
				}
			}else{
				$attachStringHTML = '<br/><fieldset><legend>'.JText::_( 'ATTACHMENTS' ).'</legend><table>';
				$attachStringText = "\n"."\n".'------- '.JText::_( 'ATTACHMENTS' ).' -------';
				foreach($this->defaultMail[$mailid]->attachments as $attachment){
					$attachStringHTML .= '<tr><td><a href="'.$attachment->url.'" target="_blank">'.$attachment->name.'</a></td></tr>';
					$attachStringText .= "\n".'-- '.$attachment->name.' ( '.$attachment->url.' )';
				}
				$attachStringHTML .= '</table></fieldset>';

				if($this->sendHTML){
					$this->Body .= $attachStringHTML;
					if(!empty($this->AltBody)) $this->AltBody .= "\n".$attachStringText;
				}else{
					$this->Body .= $attachStringText;
				}

			}
		}

		if(!empty($this->parameters)){
			$keysparams = array_keys($this->parameters);
			$this->Subject = str_replace($keysparams,$this->parameters,$this->Subject);
			$this->Body = str_replace($keysparams,$this->parameters,$this->Body);
			if(!empty($this->AltBody)) $this->AltBody = str_replace($keysparams,$this->parameters,$this->AltBody);
		}
		if(!empty($this->introtext)){
			$this->Body = $this->introtext.$this->Body;
			$this->AltBody = $this->textVersion($this->introtext).$this->AltBody;
		}


		$this->body = &$this->Body;
		$this->altbody = &$this->AltBody;
		$this->subject = &$this->Subject;
		$this->from = &$this->From;
		$this->fromName = &$this->FromName;
		$this->replyto = &$this->ReplyTo;
		$this->replyname = $this->defaultMail[$mailid]->replyname;
		$this->replyemail = $this->defaultMail[$mailid]->replyemail;
		$this->mailid = $this->defaultMail[$mailid]->mailid;
		$this->key = $this->defaultMail[$mailid]->key;
		$this->alias = $this->defaultMail[$mailid]->alias;
		$this->type = $this->defaultMail[$mailid]->type;
		$this->tempid = $this->defaultMail[$mailid]->tempid;
		$this->sentby = $this->defaultMail[$mailid]->sentby;
		$this->userid = $this->defaultMail[$mailid]->userid;
		$this->filter = $this->defaultMail[$mailid]->filter;
		$this->template = @$this->defaultMail[$mailid]->template;

		if(empty($receiver->key) && !empty($receiver->subid)){
			$receiver->key = md5(substr($receiver->email,0,strpos($receiver->email,'@')).time());
			$db = JFactory::getDBO();
			$db->setQuery('UPDATE '.acymailing_table('subscriber').' SET `key`= '.$db->Quote($receiver->key).' WHERE subid = '.(int) $receiver->subid.' LIMIT 1');
			$db->query();
		}

		$this->dispatcher->trigger('acymailing_replaceusertags',array(&$this,&$receiver,true));

		if($this->sendHTML){
			if(!empty($this->AltBody)) $this->AltBody = $this->textVersion($this->AltBody,false);
		}else{
			$this->Body = $this->textVersion($this->Body,false);
		}

		return $this->send();

	}

	function embedImages(){
		preg_match_all('/(src|background)=[\'|"]([^"\']*)[\'|"]/Ui', $this->Body, $images);
		$result = true;

		if(empty($images[2])) return $result;

		$mimetypes = array('bmp'   =>  'image/bmp',
							'gif'   =>  'image/gif',
							'jpeg'  =>  'image/jpeg',
							'jpg'   =>  'image/jpeg',
							'jpe'   =>  'image/jpeg',
							'png'   =>  'image/png',
							'tiff'  =>  'image/tiff',
							'tif'   =>  'image/tiff');

		$allimages = array();

		foreach($images[2] as $i => $url) {
			if(isset($allimages[$url])) continue;
			$allimages[$url] = 1;

			$path = $url;
			$otheracymailinglive = str_replace('http://www.','http://',ACYMAILING_LIVE);
			if($otheracymailinglive == ACYMAILING_LIVE) $otheracymailinglive = str_replace('http://','http://www.',ACYMAILING_LIVE);
			if(strpos($url,ACYMAILING_LIVE) !== false || strpos($url,$otheracymailinglive) !== false){
				$path = str_replace(array(ACYMAILING_LIVE,$otheracymailinglive,'/'),array(ACYMAILING_ROOT,ACYMAILING_ROOT,DS),urldecode($url));
			}

			$filename  = str_replace(array('%',' '),'_',basename($url));
			$md5 = md5($filename);
			$cid       = 'cid:' . $md5;
			$fileParts = explode(".", $filename);
			$ext       = strtolower($fileParts[1]);
			if(!isset($mimetypes[$ext])) continue;
			$mimeType  = $mimetypes[$ext];
			if($this->AddEmbeddedImage($path, $md5, $filename, 'base64', $mimeType)){
				 $this->Body = preg_replace("/".preg_quote($images[0][$i],'/')."/Ui", $images[1][$i]."=\"".$cid."\"", $this->Body);
			}else{
				$result = false;
			}
		}

		return $result;
	}

	function textVersion($html,$fullConvert = true){

		$html = acymailing_absoluteURL($html);

		if($fullConvert){
			$html = preg_replace('# +#',' ',$html);
			$html = str_replace(array("\n","\r","\t"),'',$html);
		}


		$removepictureslinks = "#< *a[^>]*> *< *img[^>]*> *< *\/ *a *>#isU";
		$removeScript = "#< *script(?:(?!< */ *script *>).)*< */ *script *>#isU";
		$removeStyle = "#< *style(?:(?!< */ *style *>).)*< */ *style *>#isU";
		$removeStrikeTags =  '#< *strike(?:(?!< */ *strike *>).)*< */ *strike *>#iU';
		$replaceByTwoReturnChar = '#< *(h1|h2)[^>]*>#Ui';
		$replaceByStars = '#< *li[^>]*>#Ui';
		$replaceByReturnChar1 = '#< */ *(li|td|dt|tr|div|p)[^>]*> *< *(li|td|dt|tr|div|p)[^>]*>#Ui';
		$replaceByReturnChar = '#< */? *(br|p|h1|h2|legend|h3|li|ul|dd|dt|h4|h5|h6|tr|td|div)[^>]*>#Ui';
		$replaceLinks = '/< *a[^>]*href *= *"([^#][^"]*)"[^>]*>(.+)< *\/ *a *>/Uis';

		$text = preg_replace(array($removepictureslinks,$removeScript,$removeStyle,$removeStrikeTags,$replaceByTwoReturnChar,$replaceByStars,$replaceByReturnChar1,$replaceByReturnChar,$replaceLinks),array('','','','',"\n\n","\n* ","\n","\n",'${2} ( ${1} )'),$html);

		$text = preg_replace('#(&lt;|&\#60;)([^ \n\r\t])#i','&lt; ${2}',$text);

		$text = str_replace(array(" ","&nbsp;"),' ',strip_tags($text));

		$text = trim(@html_entity_decode($text,ENT_QUOTES,'UTF-8'));

		if($fullConvert){
			$text = preg_replace('# +#',' ',$text);
			$text = preg_replace('#\n *\n\s+#',"\n\n",$text);
		}

		return $text;

	}

	function cleanText($text){
		return trim( preg_replace( '/(%0A|%0D|\n+|\r+)/i', '', (string) $text ) );
	}

	function setFrom($email,$name=''){

		if(!empty($email)){
			$this->From = $this->cleanText($email);
		}
		if(!empty($name) AND $this->config->get('add_names',true)){
			$this->FromName = $this->cleanText($name);
		}
	}

	function addParamInfo(){
		if(!empty($_SERVER)){
			$serverinfo = array();
			foreach($_SERVER as $oneKey => $oneInfo){
				$serverinfo[] = $oneKey.' => '.strip_tags(print_r($oneInfo,true));
			}
			$this->addParam('serverinfo',implode('<br />',$serverinfo));
		}

		if(!empty($_REQUEST)){
			$postinfo = array();
			foreach($_REQUEST as $oneKey => $oneInfo){
				$postinfo[] = $oneKey.' => '.strip_tags(print_r($oneInfo,true));
			}
			$this->addParam('postinfo',implode('<br />',$postinfo));
		}
	}

	function addParam($name,$value){
		$tagName = '{'.$name.'}';
		$this->parameters[$tagName] = $value;
	}

	function _addReplyTo($email,$name){
		if(empty($email)) return;
		$replyToName = $this->config->get('add_names',true) ? $this->cleanText(trim($name)) : '';
		$replyToEmail = trim($email);
		if(substr_count($replyToEmail,'@')>1){
			$replyToEmailArray = explode(';',str_replace(array(';',','),';',$replyToEmail));
			$replyToNameArray = explode(';',str_replace(array(';',','),';',$replyToName));
			foreach($replyToEmailArray as $i => $oneReplyTo){
				$this->AddReplyTo($this->cleanText($oneReplyTo),@$replyToNameArray[$i]);
			}
		}else{
			$this->AddReplyTo($this->cleanText($replyToEmail),$replyToName);
		}
	}
}
