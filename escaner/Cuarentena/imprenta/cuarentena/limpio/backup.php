<?php
/*------------------------------------------------------------------------
# Copyright (C) 2005-2012 WebxSolution Ltd. All Rights Reserved.
# @license - GPLv2.0
# Author: WebxSolution Ltd
# Websites:  http://www.webxsolution.com
# Terms of Use: An extension that is derived from the JoomlaCK editor will only be allowed under the following conditions: http://joomlackeditor.com/terms-of-use
# ------------------------------------------------------------------------*/ 
 
/**
 * Modified for use as the J plugin installer
 * AW
 */

defined( '_JEXEC' ) or die;
defined('JPATH_BASE') or die();

define('JCKPATH_COMPONENT', JPATH_ADMINISTRATOR.'/components/com_jckman');

class JCKRestorerBackup extends JObject
{
	function __construct(&$parent)
	{
		$this->parent =& $parent;
	}
	
	/**
	 * Custom install method
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 * Minor alteration - see below
	 */
	function install()
	{
		// Get a database connector object
		$db = $this->parent->getDBO();

		// Get the extension manifest object

		$manifest =& $this->parent->getManifest();
		$this->manifest =&  $manifest;//$manifest->document;
		
		/**
		 * ---------------------------------------------------------------------------------------------
		 * Manifest Document Setup Section
		 * ---------------------------------------------------------------------------------------------
		 */
		// Set the component name
		$this->set('name','');
		
		// Get the component description
		$description = & $this->manifest->description;
		$this->parent->set('message', '' );

		$element =& $this->manifest->files;

		$this->parent->setPath('extension_root', JCKPATH_COMPONENT.'/editor');

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Filesystem Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */
		// If the extension directory does not exist, lets create it
		$created = false;
		if (!file_exists($this->parent->getPath('extension_root'))) {
			if (!$created = JFolder::create($this->parent->getPath('extension_root'))) {
				$this->parent->abort('Plugin Install: '.JText::_('Failed to create directory').': "'.$this->parent->getPath('extension_root').'"');
				return false;
			}
		}

		/*
		 * If we created the extension directory and will want to remove it if we
		 * have to roll back the installation, lets add it to the installation
		 * step stack
		 */
		if ($created) {
			$this->parent->pushStep(array ('type' => 'folder', 'path' => $this->parent->getPath('extension_root')));
		}

		// Copy all necessary files
		if ($this->parent->parseFiles($element, -1) === false) {
			// Install failed, roll back changes
			$this->parent->abort();
			return false;
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Database Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		/*
		 * Let's run the install queries for the component
		 * If Joomla 1.5 compatible, with discreet sql files - execute appropriate
		 * file for utf-8 support or non-utf-8 support
		 */
		// Try for Joomla 1.5 type queries
		// Second argument is the utf compatible version attribute
		if (isset($this->manifest->install->sql))
		{
			$utfresult = $this->parent->parseSQLFiles($this->manifest->install->sql);
				
			if ($utfresult === false)
			{
				// Install failed, rollback changes
				$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_COMP_INSTALL_SQL_ERROR', $db->stderr(true)));

				return false;
			}
		}

		// Parse optional tags -- language files for plugins
		$this->parent->parseLanguages($this->manifest->languages, 0);

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Finalization and Cleanup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		//Copy JCK Editor's Manifest backup file to the Editor 
		jimport('joomla.filesystem.file');

		$src 	= JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jckman'.DS.'editor'.DS.'plugins'.DS.'jckeditor.xml';
		$dest 	= JPATH_PLUGINS.DS.'editors'.DS.'jckeditor'.DS.'jckeditor.xml';

		if( !JFile::copy( $src, $dest) ){
			$this->parent->abort( JText::_('Unable to copy JCK Editor\'s Manifest file') );
		}

		//copy toolbar to Editor
		$src 	= JPATH_ADMINISTRATOR.DS.'components' .DS. 'com_jckman' .DS. 'editor'.DS.'toolbar';
		$dest	= JPATH_PLUGINS.DS.'editors'.DS.'jckeditor'.DS.'jckeditor'.DS.'includes'.DS.'ckeditor'.DS.'toolbar';

		if( !JFolder::copy( $src, $dest,'',true) ){
			$mainframe->enqueueMessage( JText::_('Unable to copy JCK Editor\'s toolbars to editors') );
		}

		// Lastly, we will copy the manifest file to its appropriate place.
		if (!$this->parent->copyManifest(-1)) {
			// Install failed, rollback changes
			$this->parent->abort('Import: '.JText::_('Could not copy setup file'));
			return false;
		}

		return true;
	}

	/**
	 * Custom rollback method
	 * 	- Roll back the plugin item
	 *
	 * @access	public
	 * @param	array	$arg	Installation step to rollback
	 * @return	boolean	True on success
	 * @since	1.5
	 * Minor changes to the db query
	 */
	function _rollback_plugin($arg)
	{
		return true;
	}
}