<?php
/*
* Copyright (c) 2003-2009, CKSource - Frederico Knabben. All rights reserved.
* For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * define required constants
 */
error_reporting(E_ERROR); 
 
require_once "constants.php";

require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );

/* Load in the configuation file */
require_once( JPATH_CONFIGURATION	.DS.'configuration.php' );

define('JPATH_PLATFORM',JPATH_LIBRARIES);
/*load loader class */
require_once(JPATH_LIBRARIES .DS.'loader.php' );

defined('_JREQUEST_NO_CLEAN',1);

if(file_exists(JPATH_LIBRARIES.'/import.php'))
	require_once JPATH_LIBRARIES.'/import.php';
elseif(file_exists(JPATH_LIBRARIES.'/joomla/import.php'))
	require_once JPATH_LIBRARIES.'/joomla/import.php';	

if(file_exists(JPATH_LIBRARIES.'/import.legacy.php'))
	require_once JPATH_LIBRARIES.'/import.legacy.php';
		
// Botstrap the CMS libraries.
if(file_exists(JPATH_LIBRARIES.'/cms.php'))
	require_once JPATH_LIBRARIES.'/cms.php';
	
if(!class_exists('JVersion')) {
	if(file_exists(JPATH_ROOT.'/includes/version.php'))
		require JPATH_ROOT.'/includes/version.php';
}

/**
 * we need this class in each call
 */
require_once CKEDITOR_CONNECTOR_LIB_DIR . "/CommandHandler/CommandHandlerBase.php";
/**
 * singleton factory
 */
require_once CKEDITOR_CONNECTOR_LIB_DIR . "/Core/Factory.php";
/**
 * utils class
 */
require_once CKEDITOR_CONNECTOR_LIB_DIR . "/Utils/Misc.php";

/* load JCK loader class*/
require_once (CKEDITOR_INCLUDES_DIR . '/loader.php');

/** load joomla core classes **/

jimport('joomla.filter.filterinput');
jimport('joomla.language.language');
jimport('joomla.environment.uri');
jimport('joomla.environment.request');
jimport('joomla.environment.response');
jimport('joomla.user.user');
jimport('joomla.application.component.model');
jimport('joomla.html.parameter');
//This is required for the User Params
jimport( 'joomla.utilities.arrayhelper' );


//lets set DB configuration
$config = new JConfig();
// Get the global configuration object
$registry = JFactory::getConfig();
// Load the configuration values into the registry
$registry->loadObject($config);

//lets set session
jckimport('ckeditor.user.user');
$session = JCKUser::getSession();


/*** End load joomla core classe **/

/**
 * Simple function required by config.php - discover the server side path
 * to the directory relative to the "$baseUrl" attribute
 *
 * @package CKEditor
 * @subpackage Connector
 * @param string $baseUrl
 * @return string
 */
function resolveUrl($baseUrl) {
    $fileSystem =& CKEditor_Connector_Core_Factory::getInstance("Utils_FileSystem");
    return $fileSystem->getDocumentRootPath() . $baseUrl;
}

$utilsSecurity =& CKEditor_Connector_Core_Factory::getInstance("Utils_Security");
$utilsSecurity->getRidOfMagicQuotes();

/**
 * $config must be initialised
 */
$config = array();
/**
 * read config file
 */
require_once CKEDITOR_CONNECTOR_CONFIG_FILE_PATH;

CKEditor_Connector_Core_Factory::initFactory();
$connector =& CKEditor_Connector_Core_Factory::getInstance("Core_Connector");

if (isset($_GET['command'])) {
    $connector->executeCommand($_GET['command']);
}
else {
    $connector->handleInvalidCommand();
}
