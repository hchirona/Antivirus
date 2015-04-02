<?php
/**
 * ------------------------------------------------------------------------
 * JA T3v3 System Plugin
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Radio List Element
 *
 * @package  JAT3.Core.Element
 */
class JFormFieldJaPositions extends JFormField
{
	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	protected $type = 'JaPositions';

	/**
	 * Check and load assets file if needed
	 */
	function loadAsset(){
		if (!defined ('_JA_DEPEND_ASSET_')) {
			define ('_JA_DEPEND_ASSET_', 1);
			$uri = str_replace('\\', '/', str_replace( JPATH_SITE, JURI::base(), dirname(__FILE__) ));
			$uri = str_replace('/administrator/', '', $uri);
			
			if(!defined('T3V3')){
				$jdoc = JFactory::getDocument();
				$jdoc->addStyleSheet($uri.'/css/jadepend.css');
				$jdoc->addScript($uri.'/js/jadepend.js');    
			}
		}
	}
	
	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	function getInput()
	{
		$this->loadAsset();

		/*
		$db = JFactory::getDBO();
		$query = "SELECT DISTINCT position FROM #__modules ORDER BY position ASC";
		$db->setQuery($query);
		$groups = $db->loadObjectList();

		$groupHTML = array();
		if($this->element['show_empty']){
			$groupHTML[] = JHTML::_('select.option', '', '');
		}

		if($this->element['show_none']){
			$groupHTML[] = JHTML::_('select.option', 'none', JText::_('JNONE'));
		}

		if ($groups && count($groups)) {
			foreach ($groups as $v=>$t) {
				if(!empty($t->position)){
					$groupHTML[] = JHTML::_('select.option', $t->position, $t->position);
				}
			}
		}
		*/

		$template = T3V3_TEMPLATE;
		$path = JPATH_SITE;
		$lang = JFactory::getLanguage();
		$lang->load('tpl_'.$template.'.sys', $path, null, false, false)
			||  $lang->load('tpl_'.$template.'.sys', $path.'/templates/'.$template, null, false, false)
			||  $lang->load('tpl_'.$template.'.sys', $path, $lang->getDefault(), false, false)
			||  $lang->load('tpl_'.$template.'.sys', $path.'/templates/'.$template, $lang->getDefault(), false, false);
			
		$options = array();
		if($this->element['show_empty']){
			$options[] = JHTML::_('select.option', '', '');
		}

		if($this->element['show_none']){
			$options[] = JHTML::_('select.option', 'none', JText::_('JNONE'));
		}

		$positions = self::getPositions($template);
		foreach ($positions as $position)
		{
			// Template translation
			
			$langKey = strtoupper('TPL_' . $template . '_POSITION_' . $position);
			$text = JText::_($langKey);

			// Avoid untranslated strings
			if ($langKey === $text)
			{
				// Modules component translation
				$langKey = strtoupper('COM_MODULES_POSITION_' . $position);
				$text = JText::_($langKey);

				if ($langKey === $text)
				{
					// Try to humanize the position name
					$text = ucfirst(preg_replace('/^' . $template . '\-/', '', $position));
					$text = ucwords(str_replace(array('-', '_'), ' ', $text));
				}
			}

			$text = $text . ' [' . $position . ']';
			$options[] = JHTML::_('select.option', $position, $text);
		}
		
		$lists = JHTML::_('select.genericlist', $options, $this->name . ($this->element['multiple'] == 1 ? '[]' : ''), ($this->element['multiple'] == 1 ? 'multiple="multiple" size="10" ' : '') . ($this->element['disabled'] ? 'disabled="disabled"' : ''), 'value', 'text', $this->value);
		
		return $lists;
	}

	public static function getPositions($template = '')
	{
		$positions = array();

		$templateBaseDir = JPATH_SITE;
		$filePath = JPath::clean($templateBaseDir . '/templates/' . $template . '/templateDetails.xml');

		if (is_file($filePath))
		{
			// Read the file to see if it's a valid component XML file
			$xml = simplexml_load_file($filePath);
			if (!$xml)
			{
				return false;
			}

			// Check for a valid XML root tag.

			// Extensions use 'extension' as the root tag.  Languages use 'metafile' instead

			if ($xml->getName() != 'extension' && $xml->getName() != 'metafile')
			{
				unset($xml);
				return false;
			}

			$positions = (array) $xml->positions;

			if (isset($positions['position']))
			{
				$positions = $positions['position'];
			}
			else
			{
				$positions = array();
			}
		}

		return $positions;
	}

	/**
	 * Create and return a new Option
	 *
	 * @param   string  $value  The option value [optional]
	 * @param   string  $text   The option text [optional]
	 *
	 * @return  object  The option as an object (stdClass instance)
	 *
	 * @since   3.0
	 */
	public static function createOption($value = '', $text = '')
	{
		if (empty($text))
		{
			$text = $value;
		}

		$option = new stdClass;
		$option->value = $value;
		$option->text  = $text;

		return $option;
	}
}