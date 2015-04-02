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
// no direct access
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 

/**
 *
 * JA TAB AJAX CLASS
 * @author JoomlArt
 *
 */
class JATAB_Ajax
{


    /**
     *
     * Load Content Article
     */
    function content()
    {
        $tab = JRequest::getInt('tab', 0);
        if (!(int) $tab) {
            $result['text'] = '';
        } else {
            $row = array();
            $row = $this->getList($tab);
            if (!$row) {
                $result['text'] = '';
            } else {
                if (!isset($_REQUEST['view']) || $_REQUEST['view'] != 'fulltext') {
                    $result = JHtml::_('content.prepare', $row->introtext);
                } else {
                    $result = JHtml::_('content.prepare', $row->introtext . $row->fulltext);
                }
            }
        }
        return $result;

    }


    /**
     *
     * Load Content Modules
     */
    function modules()
    {
        $tab = JRequest::getInt('tab', 0);
        jimport('joomla.application.module.helper');
        jimport('joomla.application.component.model');
        $modules = $this->getModule($tab);

        $result = '';
        if (count($modules) > 0) {
            $result = JModuleHelper::renderModule($modules[0]);
        }
        return $result;
    }


    /**
     *
     * Get module data
     * @param int $id module id
     * @return array
     */
    function getModule($id)
    {
        static $clean = null;

        if (isset($clean)) {
            return $clean;
        }

        $Itemid = JRequest::getInt('Itemid');
        $app = JFactory::getApplication();
        $user = JFactory::getUser();
        $groups = implode(',', $user->authorisedLevels());
        $db = JFactory::getDbo();

        $query = $db->getQuery(true);
        $query->select('id, title, module, position, content, showtitle, params, mm.menuid');
        $query->from('#__modules AS m');
        $query->join('LEFT', '#__modules_menu AS mm ON mm.moduleid = m.id');
        $query->where('m.published = 1');
        $query->where('m.id = ' . $id);
        $date = JFactory::getDate();
        $now = $date->toMySQL();
        $nullDate = $db->getNullDate();
        $query->where('(m.publish_up = ' . $db->Quote($nullDate) . ' OR m.publish_up <= ' . $db->Quote($now) . ')');
        $query->where('(m.publish_down = ' . $db->Quote($nullDate) . ' OR m.publish_down >= ' . $db->Quote($now) . ')');

        $clientid = (int) $app->getClientId();

        if (!$user->authorise('core.admin', 1)) {
            $query->where('m.access IN (' . $groups . ')');
        }
        $query->where('m.client_id = ' . $clientid);
        if (isset($Itemid)) {
            $query->where('(mm.menuid = ' . (int) $Itemid . ' OR mm.menuid <= 0)');
        }
        $query->order('position, ordering');

        // Filter by language
        if ($app->isSite() && $app->getLanguageFilter()) {
            $query->where('m.language in (' . $db->Quote(JFactory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')');
        }

        // Set the query
        $db->setQuery($query);

        $cache = JFactory::getCache('com_modules', 'callback');
        $cacheid = md5(serialize(array($Itemid, $groups, $clientid, JFactory::getLanguage()->getTag())));

        $modules = $cache->get(array($db, 'loadObjectList'), null, $cacheid, false);
        if (null === $modules) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::sprintf('JLIB_APPLICATION_ERROR_MODULE_LOAD', $db->getErrorMsg()));
            $return = false;
            return $return;
        }

        // Apply negative selections and eliminate duplicates
        $negId = $Itemid ? -(int) $Itemid : false;
        $dupes = array();
        $clean = array();
        for ($i = 0, $n = count($modules); $i < $n; $i++) {
            $module = &$modules[$i];

            // The module is excluded if there is an explicit prohibition, or if
            // the Itemid is missing or zero and the module is in exclude mode.
            $negHit = ($negId === (int) $module->menuid) || (!$negId && (int) $module->menuid < 0);

            if (isset($dupes[$module->id])) {
                // If this item has been excluded, keep the duplicate flag set,
                // but remove any item from the cleaned array.
                if ($negHit) {
                    unset($clean[$module->id]);
                }
                continue;
            }
            $dupes[$module->id] = true;

            // Only accept modules without explicit exclusions.
            if (!$negHit) {
                //determine if this is a custom module
                $file = $module->module;
                $custom = substr($file, 0, 4) == 'mod_' ? 0 : 1;
                $module->user = $custom;
                // Custom module name is given by the title field, otherwise strip off "com_"
                $module->name = $custom ? $module->title : substr($file, 4);
                $module->style = null;
                $module->position = strtolower($module->position);
                $clean[$module->id] = $module;
            }
        }
        unset($dupes);
        // Return to simple indexing that matches the query order.
        $clean = array_values($clean);

        return $clean;
    }


    /**
     *
     * Get article data
     * @param int $id
     * @return object
     */
    function getList($id = 0)
    {
        // Get the dbo
        $db = JFactory::getDbo();

        require_once JPATH_BASE . DS . 'components' . DS . 'com_content' . DS . 'models' . DS . 'article.php';
        $model = new ContentModelArticle();
        $row = $model->getItem($id);

        return $row;
    }
}