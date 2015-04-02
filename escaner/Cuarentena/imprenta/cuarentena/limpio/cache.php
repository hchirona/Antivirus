<?php
/*------------------------------------------------------------------------
# Copyright (C) 2005-2012 WebxSolution Ltd. All Rights Reserved.
# @license - GPLv2.0
# Author: WebxSolution Ltd
# Websites:  http://www.webxsolution.com
# Terms of Use: An extension that is derived from the JoomlaCK editor will only be allowed under the following conditions: http://joomlackeditor.com/terms-of-use
# ------------------------------------------------------------------------*/ 

defined( '_JEXEC' ) or die( 'Restricted access' );

class JCKCache
{
	static function  getInstance($group = '', $handler = 'callback', $storage = null)
    {
        static $instances = array();
	        
        $hash = md5($group.$handler.$storage);
	        
        $handler = ($handler == 'function') ? 'callback' : $handler;

		$options = array('defaultgroup'	=> $group, 
                         'cachebase'=>JPATH_CONFIGURATION.DS.'cache',
                         'caching' => true); //caching has to be on

		if (isset($storage)) {
			$options['storage'] = $storage;
		}

		jimport('joomla.cache.cache');

		$instances[$hash] = JCache::getInstance($handler, $options);

		return $instances[$hash];
    
    }
    
} 


