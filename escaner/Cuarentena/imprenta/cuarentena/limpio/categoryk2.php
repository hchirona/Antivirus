<?php
/**
 * ------------------------------------------------------------------------
 * JA Slideshow Module for J25 & J30
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
/**
 * JA Param K2 Helper
 *
 * @since      Class available since Release 1.2.0
 */
class JFormFieldCategoryk2 extends JFormField
{
    /*
	 * Category K2 name
	 *
	 * @access	protected
	 * @var		string
	 */
    var $type = 'Categoryk2';


    /**
     *
     * process input params
     * @return string element param
     */
    
    function loadAsset(){
        if (!defined ('_JA_DEPEND_ASSET_')) {
            define ('_JA_DEPEND_ASSET_', 1);
            $uri = str_replace(DIRECTORY_SEPARATOR, '/', str_replace( JPATH_SITE, JURI::base(), dirname(__FILE__) ));
            $uri = str_replace('/administrator/', '', $uri);
            
            //mootools support joomla 1.7 and 2.5
            JHTML::_('behavior.framework', true);
            
            JHTML::stylesheet($uri.'/assets/css/jadepend.css');
            JHTML::script($uri.'/assets/js/jadepend.js');
        }
    }

    protected function getInput()
    {
        $this->loadAsset();
        
        $func = (string) $this->element['function'] ? (string) $this->element['function'] : '';
        $value = $this->value ? $this->value : (string) $this->element['default'];
       //  var_dump($func); die;
        if (substr($func, 0, 1) == '@') {
            $func = substr($func, 1);
            if (method_exists($this, $func)) {
                return $this->$func();
            }
        } else {
            $subtype = (isset($this->element['subtype'])) ? trim($this->element['subtype']) : '';
            if (method_exists($this, $subtype)) {
                return $this->$subtype();
            }
        }
        return;
    }


    /**
     * Fetch Ja Element K2 Catetgory Param method
     *
     * @return	object  param
     */
    function getCategory()
    {
        $control_name = 'jform';
        $name = $this->element['name'];
        $flag = false;
        if (!$this->checkComponent('com_k2')) {
			return '<input type="hidden" name="' . $control_name . '[params][' . $name . '][]" id="' . $control_name . '_params_' . $name . '"/> <span style="color:red; float:left">K2 component is not installed!</span>';
        }

        $categories = JFormFieldCategoryK2::_fetchElement(0, '', array());

        $HTMLSelect = '<select name="' . $control_name . '[params][' . $name . '][]" id="' . $control_name . '_params_' . $name . '" class="inputbox" multiple="multiple" size="10">';

        $HTMLCats = '';
        $value = $this->value;
        foreach ($categories as $item) {
			if(isset($item->id) && $item->id > 0){
				$check = '';
				if ((is_array($value) && in_array($item->id, $value)) || (!is_array($value) && $item->id == $value)) {
					$flag = true;
					$check = 'selected="selected"';
				}

				$class = '';

				if ($item->parent != 0)
					$class = 'class="subcat"';

				$HTMLCats .= '<option value="' . $item->id . '" ' . $check . ' ' . $class . '>' . '&nbsp;&nbsp;&nbsp;' . $item->treename . ' (ID: ' . $item->id . ')' . '</option>';
			}	
        }
        if ($flag == true) {
            $HTMLSelect .= '<option value="0">' . JText::_("SELECT_CATEGORY_ALL") . '</option>';
        } else {
            $HTMLSelect .= '<option value="0" selected="selected">' . JText::_("SELECT_CATEGORY_ALL") . '</option>';
        }
        $HTMLSelect .= $HTMLCats;
        $HTMLSelect .= '</select>';
        return $HTMLSelect;
    }


    /**
     *
     * Get data from Sub Category K2 database
     * @param int $parent parent category id
     * @return array list object categories child
     */
    function fetchChild($parent)
    {
        $mainframe = JFactory::getApplication();
    	$user = JFactory::getUser();
        $aid = (int)$user->get('aid');
    	$db = JFactory::getDBO();
        $query = "SELECT * FROM #__k2_categories WHERE parent = '{$parent}' ";
    	$query .= " AND published=1 
								AND trash=0";
        $query .=" ORDER BY ordering ASC";
        $db->setQuery($query);
        $cats = $db->loadObjectList();

        return $cats;
    }


    /**
     *
     * Show element data on K2
     * @param int $id
     * @param strig $indent
     * @param array $list
     * @param int $maxlevel
     * @param int $level
     * @param int $type
     * @return array list categories element
     */
    function _fetchElement($id, $indent, $list, $maxlevel = 9999, $level = 0, $type = 1)
    {
        $children = JFormFieldCategoryK2::fetchChild($id);

        if (@$children && $level <= $maxlevel) {
            foreach ($children as $v) {
                $id = $v->id;

                if ($type) {
                    $pre = '<sup>|_</sup>&nbsp;';
                    $spacer = '.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                } else {
                    $pre = '- ';
                    $spacer = '&nbsp;&nbsp;';
                }

                if ($v->parent == 0) {
                    $txt = $v->name;
                } else {
                    $txt = $pre . $v->name;
                }
                $pt = $v->parent;
                $list[$id] = $v;
                $list[$id]->treename = "{$indent}{$txt}";
                $list[$id]->children = count(@$children);
                $list[$id]->haschild = true;
                $list = JFormFieldCategoryK2::_fetchElement($id, $indent . $spacer, $list, $maxlevel, $level + 1, $type);
            }
        } else {
            $list[$id]->haschild = false;
        }
        return $list;
    }


    /**
     *
     * Check component is existed
     * @param string $component component name
     * @return int return > 0 when component is installed
     */
    function checkComponent($component)
    {
        $db = JFactory::getDBO();
        $query = " SELECT Count(*) FROM #__extensions as e WHERE e.element ='$component' and e.enabled=1";
        $db->setQuery($query);
	    return $db->loadResult();
    }
}
?>