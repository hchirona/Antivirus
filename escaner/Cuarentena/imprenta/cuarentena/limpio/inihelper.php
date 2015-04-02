<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009-2013 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 *
 */

// Protection against direct access
defined('AKEEBAENGINE') or die();

/**
 * A class to load INI files describing the various Akeeba engines and GUI definitions,
 * along with their parameters.
 */
class AEUtilInihelper {

	/** Do not allow object instances */
	private function __construct() {}

	/**
	 * Returns a hash list of Akeeba engines and their data. Each entry has the engine
	 * name as key and contains two arrays, under the 'information' and 'parameters' keys.
	 * @param string $engine_type The engine type to return information for
	 * @return array
	 */
	public static function getEnginesList($engine_type)
	{
		// This is a static cache which persists between subsequent calls, but not
		// between successive page loads.
		static $engine_list = array();

		// Try to serve cached data first
		if(isset($engine_list[$engine_type])) return $engine_list[$engine_type];

		// Find absolute path to normal and plugins directories
		$ds = DIRECTORY_SEPARATOR;
		$path_list = array(
			AEFactory::getAkeebaRoot().$ds.'engines'.$ds.$engine_type,
			AEFactory::getAkeebaRoot().$ds.'plugins'.$ds.'engines'.$ds.$engine_type
		);

		// Initialize the array where we store our data
		$engine_list[$engine_type] = array();

		// Loop for the paths where engines can befound
		foreach($path_list as $path)
		{
			if(is_dir($path))
			{
				if(is_readable($path))
				{
					if( $handle = @opendir($path) )
					{
						while(false !== $filename = @readdir($handle))
						{
							if( (strtolower(substr($filename, -4)) == '.ini') && @is_file($path.$ds.$filename) )
							{
								$information = array();
								$parameters = array();
								AEUtilINI::parseEngineINI($path.$ds.$filename, $information, $parameters);
								$engine_name = substr($filename, 0, strlen($filename) - 4);
								$engine_list[$engine_type][$engine_name] = array(
									'information' => $information,
									'parameters' => $parameters
								);
							}
						} // while readdir
						@closedir($handle);
					} // if opendir
				} // if readable
			} // if is_dir
		}

		return $engine_list[$engine_type];
	}

	/**
	 * Parses the GUI INI files and returns an array of groups and their data
	 * @return array
	 */
	public static function getGUIGroups()
	{
		// This is a static cache which persists between subsequent calls, but not
		// between successive page loads.
		static $gui_list = array();

		// Try to serve cached data first
		if(!empty($gui_list) && is_array($qui_list) )
		{
			if(count($gui_list) > 0) return $gui_list;
		}

		// Find absolute path to normal and plugins directories
		$ds = DIRECTORY_SEPARATOR;
		$path_list = array(
			AEFactory::getAkeebaRoot().'/core'
		);
		
		if(AKEEBA_PRO) {
			$path_list[] = AEFactory::getAkeebaRoot().'/plugins/core';
		}
		
		// Add platform configuration files
		$platform_paths = AEPlatform::getInstance()->getPlatformDirectories();
		foreach($platform_paths as $p) {
			$path_list[] = $p.'/config';
		}

		// Initialize the array where we store our data
		$gui_list = array();

		// Loop for the paths where engines can be found
		foreach($path_list as $path)
		{
			if(is_dir($path))
			{
				if(is_readable($path))
				{
					if( $handle = @opendir($path) )
					{
						// Store INI names in temp array because we'll sort based on filename (GUI order IS IMPORTANT!!)
						$allINIs = array();
						while(false !== $filename = @readdir($handle))
						{
							if( (strtolower(substr($filename, -4)) == '.ini') && @is_file($path.$ds.$filename) )
							{
								$allINIs[] = $path.$ds.$filename;
							}
						} // while readdir
						@closedir($handle);
						if(!empty($allINIs))
						{
							// Sort GUI files alphabetically
							asort($allINIs);
							// Include each GUI def file
							foreach($allINIs as $filename)
							{
								$information = array();
								$parameters = array();
								AEUtilINI::parseInterfaceINI($filename, $information, $parameters);
								// This effectively skips non-GUI INIs (e.g. the scripting INI)
								if(!empty($information['description']))
								{
									$group_name = substr(basename($filename), 0, - 4);
									$gui_list[$group_name] = array(
										'information' => $information,
										'parameters' => $parameters
									);
								}
							}
						}

					} // if opendir
				} // if readable
			} // if is_dir
		}
		
		ksort($gui_list);
		
		// Push stack filter settings to the 03.filters section
		$path_list = array(
			AEFactory::getAkeebaRoot().$ds.'platform'.$ds.'filters'.$ds.'stack',
			AEFactory::getAkeebaRoot().$ds.'filters'.$ds.'stack',
			AEFactory::getAkeebaRoot().$ds.'plugins'.$ds.'filters'.$ds.'stack'
		);
		$platform_paths = AEPlatform::getInstance()->getPlatformDirectories();
		foreach($platform_paths as $p) {
			$path_list[] = $p.'/filters/stack';
		}
		// Loop for the paths where optional filters can be found
		foreach($path_list as $path)
		{
			if(is_dir($path))
			{
				if(is_readable($path))
				{
					if( $handle = @opendir($path) )
					{
						// Store INI names in temp array because we'll sort based on filename (GUI order IS IMPORTANT!!)
						$allINIs = array();
						while(false !== $filename = @readdir($handle))
						{
							if( (strtolower(substr($filename, -4)) == '.ini') && @is_file($path.$ds.$filename) )
							{
								$allINIs[] = $path.$ds.$filename;
							}
						} // while readdir
						@closedir($handle);
						if(!empty($allINIs))
						{
							// Sort filter files alphabetically
							asort($allINIs);
							// Include each filter def file
							foreach($allINIs as $filename)
							{
								$information = array();
								$parameters = array();
								AEUtilINI::parseInterfaceINI($filename, $information, $parameters);
								if(!empty($gui_list['03.filters']['parameters'])) {
									/*
									$gui_list['03.filters']['parameters'][] = array(
										'title' => '',
										'description' => '',
										'type' => 'separator',
										'default' => ''
									);
									*/
								}
								if(!array_key_exists('03.filters', $gui_list)) {
									$gui_list['03.filters'] = array('parameters' => array());
								}
								if(!array_key_exists('parameters', $gui_list['03.filters'])) {
									$gui_list['03.filters']['parameters'] = array();
								}
								if(!is_array($parameters)) {
									$parameters = array();
								}
								$gui_list['03.filters']['parameters'] = array_merge( $gui_list['03.filters']['parameters'], $parameters );
							}
						}
					} // if opendir
				} // if readable
			} // if is_dir
		}
		
		return $gui_list;
	}

	public static function getInstallerList()
	{
		// This is a static cache which persists between subsequent calls, but not
		// between successive page loads.
		static $installer_list = array();

		// Try to serve cached data first
		if(!empty($installer_list) && is_array($installer_list))
		{
			if(count($installer_list) > 0) return $installer_list;
		}

		// Find absolute path to normal and plugins directories
		$ds = DIRECTORY_SEPARATOR;
		$path_list = array(
			AEPlatform::getInstance()->get_installer_images_path()
		);

		// Initialize the array where we store our data
		$installer_list = array();

		// Loop for the paths where engines can be found
		foreach($path_list as $path)
		{
			if(is_dir($path))
			{
				if(is_readable($path))
				{
					if( $handle = @opendir($path) )
					{
						while(false !== $filename = @readdir($handle))
						{
							if( (strtolower(substr($filename, -4)) == '.ini') && @is_file($path.$ds.$filename) )
							{
								$data = AEUtilINI::parse_ini_file($path.$ds.$filename, true);
								foreach($data as $key => $values)
								{
									$installer_list[$key] = array();
									foreach($values as $key2 => $value)
									{
										$installer_list[$key][$key2]=$value;
									}
								}
							}
						} // while readdir
						@closedir($handle);
					} // if opendir
				} // if readable
			} // if is_dir
		}

		return $installer_list;
	}

	/**
	 * Returns the JSON representation of the GUI definition and the associated values
	 * @return string
	 */
	public static function getJsonGuiDefinition()
	{
		// Initialize the array which will be converted to JSON representation
		$json_array = array(
			'engines' => array(),
			'installers' => array(),
			'gui' => array()
		);

		// Get a reference to the configuration
		$configuration = AEFactory::getConfiguration();

		// Get data for all engines
		$engine_types = array('archiver','dump','scan','writer','proc');
		foreach($engine_types as $type)
		{
			$engines = self::getEnginesList($type);
			foreach($engines as $engine_name => $engine_data)
			{
				// Translate information
				foreach($engine_data['information'] as $key => $value)
				{
					switch($key)
					{
						case 'title':
						case 'description':
							$value = AEPlatform::getInstance()->translate($value);
							break;
					}
					$json_array['engines'][$type][$engine_name]['information'][$key] = $value;
				}
				// Process parameters
				$parameters = array();
				foreach($engine_data['parameters'] as $param_key => $param)
				{
					$param['default'] = $configuration->get( $param_key, $param['default'], false );
					foreach($param as $option_key => $option_value )
					{
						// Translate title, description, enumkeys
						switch($option_key)
						{
							case 'title':
							case 'description':
							case 'labelempty':
							case 'labelnotempty':
								$param[$option_key] = AEPlatform::getInstance()->translate($option_value);
								break;

							case 'enumkeys':
								$enumkeys = explode('|', $option_value);
								$new_keys = array();
								foreach($enumkeys as $old_key)
								{
									$new_keys[] = AEPlatform::getInstance()->translate($old_key);
								}
								$param[$option_key] = implode('|', $new_keys);
								break;

							default:

						}
					}
					$parameters[$param_key] = $param;
				}
				// Add processed parameters
				$json_array['engines'][$type][$engine_name]['parameters'] = $parameters;
			}
		}

		// Get data for GUI elements
		$json_array['gui'] = array();
		$groupdefs = self::getGUIGroups();
		foreach($groupdefs as $group_ini => $definition)
		{
			$group_name = AEPlatform::getInstance()->translate($definition['information']['description']);
			if( empty($group_name) ) continue; // Skip no-name groups
			$parameters = array();
			foreach($definition['parameters'] as $param_key => $param)
			{
					$param['default'] = $configuration->get( $param_key, $param['default'], FALSE );
					foreach($param as $option_key => $option_value )
					{
						// Translate title, description, enumkeys
						switch($option_key)
						{
							case 'title':
							case 'description':
								$param[$option_key] = AEPlatform::getInstance()->translate($option_value);
								break;

							case 'enumkeys':
								$enumkeys = explode('|', $option_value);
								$new_keys = array();
								foreach($enumkeys as $old_key)
								{
									$new_keys[] = AEPlatform::getInstance()->translate($old_key);
								}
								$param[$option_key] = implode('|', $new_keys);
								break;

							default:

						}
					}
					$parameters[$param_key] = $param;
			}
			$json_array['gui'][$group_name] = $parameters;
		}

		// Get data for the installers
		$json_array['installers'] = self::getInstallerList();

		$json = json_encode($json_array);

		return $json;
	}
}
?>