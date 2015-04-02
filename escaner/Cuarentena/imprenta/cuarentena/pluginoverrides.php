<?php
/*------------------------------------------------------------------------
# Copyright (C) 2005-2012 WebxSolution Ltd. All Rights Reserved.
# @license - GPLv2.0
# Author: WebxSolution Ltd
# Websites:  http://www.webxsolution.com
# Terms of Use: An extension that is derived from the JoomlaCK editor will only be allowed under the following conditions: http://joomlackeditor.com/terms-of-use
# ------------------------------------------------------------------------*/ 

// no direct access
defined( '_JEXEC' ) or die();

error_reporting(E_ERROR); 

jimport('joomla.event.plugin');
jimport('joomla.html.parameter');
jckimport('ckeditor.htmlwriter.javascript');

require(JPATH_ADMINISTRATOR.'/components/com_jckman/config/handler.php');
require(JPATH_ADMINISTRATOR.'/components/com_jckman/helper.php');

class plgEditorPluginOverrides extends JPlugin 
{
  	function plgEditorPluginOverrides(& $subject, $config) 
	{
		parent::__construct($subject, $config);
	}

	function beforeLoad(&$registry)
	{
		$db = JFactory::getDBO();

		$query = 'SELECT * FROM #__jckplugins WHERE published = 1';

		$db->setQuery( $query );
		$plugins = $db->loadObjectList();

		if (!is_array($plugins)) {
			JCKHelper::error( $db->getErrorMsg() );
		}

		if(empty($plugins))
			return;

			//lets create JS object
		$javascript = new JCKJavascript();
		$script = "CKEDITOR.jckplugins = {";
		
		foreach($plugins as $plugin)
		{
			if(empty($plugin->params) || $plugin->params == '{}' )
				continue;

			if($plugin->iscore)
                $params = new JCKParameter(trim($plugin->params),JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jckman'.DS.'editor'.DS.'plugins'.DS.$plugin->name.'.xml');
            else
			    $params = new JCKParameter(trim($plugin->params),JPATH_PLUGINS.DS.'editors'.DS.'jckeditor'.DS.'plugins'.DS.$plugin->name.DS.$plugin->name.'.xml');
			$name = $plugin->name;

			$dialogName =  $params->get('dialogname','');
			$title = $params->get('dialogtitle','');
			$height = $params->get('height','');
			$width = $params->get('width','');
			$resizable = $params->get('resizable','');

			if($dialogName)
				$name = $dialogName; // overrwite plugin name with dialogname

			//lets get plugin Joomla configurable options

			if(trim((strtolower($title)) == 'default'))
			 	$title = '';		

			$options = '';
			$optionsXML = $params->getXML();

			if (isset($optionsXML['options'])) 
			{
				foreach ($optionsXML['options']->children() as $node)  
				{
					$key = $node->attributes('name');
					$default = $node->attributes('default');
					$value = $params->get($key,$default);

					$handler = JCKConfigHandler::getInstance($node->attributes('type'));
               		$options.= $handler->getOptions($key,$value,$default,$node,$params,$name);
				}
			}

			if($options)
			{
				$options = substr($options, 0, -1);
				$options = '[' . $options  . ']';
			}	
			else
				$options = 'false';

			$script .= "$name:{'title':'$title','height':'$height','width':'$width','resizable':'$resizable','options': $options},";

		}
		if($script != "CKEDITOR.jckplugins = {")
			$script = substr($script, 0, -1);
		$script .= "};" . chr(13);

        $actionscript = "

		CKEDITOR.tools.removeSlashes = function(val)
		{	
			 val = val.replace(/(\\\"|\\\')/g,'');
		     return val;
		}

		CKEDITOR.on( 'dialogDefinition', function( ev )
		{
			// Take the dialog name and its definition from the event
			// data.
			var dialogName = ev.data.name;
			var dialogDefinition = ev.data.definition;

			if(CKEDITOR.jckplugins[dialogName ])
			{
				var jckplugin = CKEDITOR.jckplugins[dialogName ];

				if(jckplugin.title) dialogDefinition.title = jckplugin.title;
				if(jckplugin.height) dialogDefinition.minHeight = jckplugin.height;
				if(jckplugin.width) dialogDefinition.minWidth = jckplugin.width;
				if(jckplugin.resizable) dialogDefinition.resizable = jckplugin.resizable;

				if(jckplugin.options)
				{
					for(var k = 0; k < jckplugin.options.length;k++)
					{
						eval('CKEDITOR.config.' + CKEDITOR.tools.removeSlashes(jckplugin.options[k]));
					}
				}
			}
		});

		for(var m in CKEDITOR.jckplugins)
		{  
			var jckplugin = CKEDITOR.jckplugins[m];
			
			if(jckplugin.options)
			{
                for(var n = 0; n < jckplugin.options.length;n++)
				{
					eval('editor.config.' + CKEDITOR.tools.removeSlashes(jckplugin.options[n]));
				}
			}
		}
		";

		$javascript->addScriptDeclaration($script.$actionscript);
		return $javascript->toRaw();
	}

	function afterLoad(&$params)
	{
		$javascript = new JCKJavascript();

		$script = "for(var m in CKEDITOR.jckplugins)
		{  
			var jckplugin = CKEDITOR.jckplugins[m];

			if(jckplugin.options)
			{

				for(var n = 0; n < jckplugin.options.length;n++)
				{
					eval('editor.config.' + CKEDITOR.tools.removeSlashes(jckplugin.options[n]));
				}
				
			}
		}";

		$javascript->addScriptDeclaration($script);
		return $javascript->toRaw();	

	}

}

class JCKParameter extends JRegistry
{
	protected $_elementPath 	= array();
	protected $_raw 			= false;
	protected $_xml 			= false;

	public function __construct($data = '', $path = '')
	{
		parent::__construct('_default');

		// Set base path.
		$this->_elementPath[] = dirname(__FILE__) . '/parameter/element';

		if (!empty($data) && is_string($data))
		{
			$this->loadString($data);
		}

		if ($path)
		{
			$this->loadSetupFile($path);
		}

		$this->_raw = $data;
	}

	public function getXML()
	{
		return $this->_xml;
	} 

	public function setXML(&$xml)
	{
		if (is_object($xml))
		{
			if ($group = $xml->attributes('group'))
			{
				$this->_xml[$group] = $xml;
			}
			else
			{
				$this->_xml['_default'] = $xml;
			}

			if ($dir = $xml->attributes('addpath'))
			{
				$this->addElementPath(JPATH_ROOT . str_replace('/', DS, $dir));
			}
		}
	}	

	public function loadSetupFile($path)
	{
		$result = false;

		if ($path)
		{
			$xml = JCKHelper::getXMLParser('Simple');

			if ($xml->loadFile($path))
			{
				if ($params = $xml->document->params)
				{
					foreach ($params as $param)
					{
						$this->setXML($param);
						$result = true;
					}
				}
			}
		}
		else
		{
			$result = true;
		}

		return $result;
	}
}