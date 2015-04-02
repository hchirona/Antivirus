<?php
/**
 * ------------------------------------------------------------------------
 * JA Tabs Plugin for J25 & J30
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

if (!defined('_JEXEC')) {
    // no direct access
    define('_JEXEC', 1);
    defined ( '_JEXEC' ) or die ( 'Restricted access' ); 
    $path = dirname(dirname(dirname(dirname(__FILE__))));
    define('JPATH_BASE', $path);

    if (strpos(php_sapi_name(), 'cgi') !== false && !empty($_SERVER['REQUEST_URI'])) {
        //Apache CGI
        $_SERVER['PHP_SELF'] = rtrim(dirname(dirname(dirname($_SERVER['PHP_SELF']))), '/\\');
    } else {
        //Others
        $_SERVER['SCRIPT_NAME'] = rtrim(dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))), '/\\');
    }

    define('DS', DIRECTORY_SEPARATOR);
    require_once (JPATH_BASE . DS . 'includes' . DS . 'defines.php');
    require_once (JPATH_BASE . DS . 'includes' . DS . 'framework.php');
    JDEBUG ? $_PROFILER->mark('afterLoad') : null;

	/**
	 * CREATE THE APPLICATION
	 *
	 * NOTE :
	 */
	$japp = JFactory::getApplication('administrator');

	/**
	 * INITIALISE THE APPLICATION
	 *
	 * NOTE :
	 */
	$japp->initialise(array('language' => $japp->getUserState('application.lang', 'lang')));
}

$user = JFactory::getUser();

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

if(!$user->authorise('core.manage', 'com_modules'))
	$result['error'] = JText::_('NO_PERMISSION');
	echo json_encode($result);
	exit();


$task = isset($_REQUEST['jaction']) ? $_REQUEST['jaction'] : '';
if ($task != '' && method_exists(JAFileConfig, $task)) {
	JAFileConfig::$task();
}

/**
 *
 * JAFileConfig helper module class
 * @author JoomlArt
 *
 */
class JAFileConfig
{
	/**
	 *
	 * save Profile
	 */

	public static function response($result = array()){
		die(json_encode($result));
	}

	public static function error($msg = ''){
		return self::response(array(
			'error' => $msg
			));
	}

	public static function save()
	{
		// Initialize some variables
		
		$profile = JRequest::getCmd('profile');
		if (!$profile) {
			return self::error(JText::_('INVALID_DATA_TO_SAVE_PROFILE'));
		}

		$params = new JRegistry;
		$post = $_POST;
		if (isset($post)) {
			foreach ($post as $k => $v) {
				$params->set($k, $v);
			}
		}

		$file = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'profiles' . DIRECTORY_SEPARATOR . $profile . '.ini';
		if (JFile::exists($file)) {
			@chmod($file, 0777);
		}

		$data = $params->toString();
		if (!@JFile::write($file, $data)) {
			return self::error(JText::_('OPERATION_FAILED'));
		}

		return self::response(array(
			'successful' => sprintf(JText::_('SAVE_PROFILE_SUCCESSFULLY'), $profile),
			'profile' => $profile,
			'type' => 'new'
			 ));
	}

	/**
	 *
	 * Clone Profile
	 */
	function duplicate()
	{
		$profile = JRequest::getCmd('profile');
		$from = JRequest::getCmd('from');
		
		if (!$profile || !$from) {
			return self::error(JText::_('INVALID_DATA_TO_SAVE_PROFILE'));
		}

		$path = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'profiles';
		$source = $path . DIRECTORY_SEPARATOR . $from . '.ini';
		$dest = $path . DIRECTORY_SEPARATOR . $profile . '.ini';
		if (JFile::exists($dest)) {
			return self::error(sprintf(JText::_('PROFILE_EXIST'), $profile));
		}

		$result = array();
		if (JFile::exists($source)) {
			if ($error = @JFile::copy($source, $dest) == true) {
				return self::response(array(
					'successful' => JText::_('CLONE_PROFILE_SUCCESSFULLY'),
					'profile' => $profile,
					'type' => 'duplicate'
					 ));
			} else {
				return self::error($error);
			}
		} else {
			return self::error(JText::_(sprintf('PROFILE_NOT_FOUND', $from)));
		}
	}

	/**
	 *
	 * Delete a profile
	 */
	function delete()
	{
		// Initialize some variables
		$profile = JRequest::getCmd('profile');
		if (!$profile) {
			return self::error(JText::_('NO_PROFILE_SPECIFIED'));
		}

		$file = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'profiles' . DIRECTORY_SEPARATOR . $profile . '.ini';
		if (JFile::exists($file) && !@JFile::delete($file)) {
			return self::error(sprintf(JText::_('DELETE_FAIL'), $file));
		}

		return self::response(array(
			'successful' => sprintf(JText::_('DELETE_PROFILE_SUCCESSFULLY'), $profile),
			'profile' => $profile,
			'type' => 'delete'
			));
	}
}