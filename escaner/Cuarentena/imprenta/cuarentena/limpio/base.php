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
defined('_JEXEC') or die();

if (!defined('_JA_BASE_MENU_CLASS')) {
	define('_JA_BASE_MENU_CLASS', 1);

	/**
	 * JAMenuBase class
	 *
	 * @package JAT3.Core.Menu
	 */
	class JAMenuBase extends JObject
	{
		var $params = null;
		var $children = null;
		var $open = null;
		var $items = null;
		var $endlevel = 0;
		var $itemid = 0;

		/**
		 * Constructor
		 *
		 * @param array &$params  Parameters
		 *
		 * @return void
		 */
		function __construct($params)
		{
			$app = JFactory::getApplication();
			$menu = $app->getMenu();
			$active = ($menu->getActive()) ? $menu->getActive() : $menu->getDefault();
			
			if(!$active){
				return false;
			}

			$this->params = $params;
			$this->itemid = $active->id;
			$this->endlevel = $params->get('mm_end_level');
		
			if($this->endlevel < 1){
				$this->endlevel = 100;	//10 or 100 maximum level
			}
			
			//should be auto load menu
			$this->loadMenu();
			$this->genMenu();
		}

		/**
		 * Get page title
		 *
		 * @param JParameter $params  Parameter of menu
		 *
		 * @return string  Page title
		 */
		function getPageTitle($params)
		{
			return $params->get('page_title');
		}

		/**
		 * Load menu
		 *
		 * @return void
		 */
		function loadMenu()
		{
			$list = array();
			$user = JFactory::getUser();
			$app = JFactory::getApplication();
			$menu = $app->getMenu();

			//get user access level - used to check the access level setting for menu items
			$aid = $user->getAuthorisedViewLevels();
			
			// If no active menu, use default
			$active = ($menu->getActive()) ? $menu->getActive() : $menu->getDefault();
			$this->open = isset($active) ? $active->tree : array();
			
			//end level for fetching menu items
			$end = $this->endlevel;
			$rows = $menu->getItems('menutype', $this->params->get('mm_type'));

			if (!count($rows)){
				return;
			}
			
			// first pass - collect children
			$children = array();
			$cacheIndex = array();
			$this->items = array();
			
			foreach ($rows as $index => $v){
				//bypass items not in range
				if ($end > 0 && $v->level > $end) {
					continue;
				}

				$v->title = str_replace('&', '&amp;', str_replace('&amp;', '&', $v->title));
				if (in_array($v->access, $aid)) {
					$pt = $v->parent_id;
					$list = isset($children[$pt]) ? $children[$pt] : array();

					if ($this->params->get('mm_enable')) {
						$modules = $this->loadModules($v->params);
						
						if ($modules && count($modules) > 0) {
							$v->content = '';
							$total = count($modules);
							$cols = $total;

							for ($col = 0; $col < $cols; $col++) {
								$pos = ($col == 0) ? 'first' : (($col == $cols - 1) ? 'last' : '');
								if ($cols > 1) {
									$v->content .= $this->beginSubMenuModules($v, 1, $pos, $col, true);
								}
								
								$i = $col;
								while ($i < $total) {
									$mod = $modules[$i];
									if (!isset($mod->name)){
										$mod->name = $mod->module;
									}
									$i += $cols; //maybe we should detect to fix here, J2.5 have issue with <jdoc
									//$v->content .= JModuleHelper::renderModule($mod, array('style' => $v->params->get('style', 'xhtml'), 'title' => $mod->title, 'name' => $mod->name));
									$v->content .= '<jdoc:include type="module" name="'. $mod->name . '" title="' . $mod->title . '" style="' . $v->params->get('style', 'xhtml') . '" />';
								}
								
								if ($cols > 1){
									$v->content .= $this->endSubMenuModules($v, 1, true);
								}
							}

							$v->cols = $cols;
							$v->content = trim($v->content);
							$this->items[$v->id] = $v;
						}
					}

					$v->flink = $v->link;
					switch ($v->type) {
						case 'separator':
							// No further action needed.
							continue;

						case 'url':
							if ((strpos($v->link, 'index.php?') === 0) && (strpos($v->link, 'Itemid=') === false)) {
								// If this is an internal Joomla link, ensure the Itemid is set.
								$v->flink = $v->link . '&Itemid=' . $v->id;
							}
							break;

						case 'alias':
							// If this is an alias use the item id stored in the parameters to make the link.
							$v->flink = 'index.php?Itemid=' . $v->params->get('aliasoptions');
							break;

						default:
							// $router = JSite::getRouter();
							$router = JFactory::getApplication()->getRouter();
							if ($router->getMode() == JROUTER_MODE_SEF) {
								$v->flink = 'index.php?Itemid=' . $v->id;
							} else {
								$v->flink .= '&Itemid=' . $v->id;
							}
							break;
					}
					$v->url = $v->flink = JRoute::_($v->flink);

					// Handle SSL links
					
					if ($v->home == 1) {
						$v->url = JURI::base();
					} elseif (strcasecmp(substr($v->url, 0, 4), 'http') && (strpos($v->link, 'index.php?') !== false)) {
						$v->url = JRoute::_($v->url, true, $v->params->def('secure', 0));
					} else {
						$v->url = str_replace('&', '&amp;', $v->url);
					}
					
					//calculate menu column
					if (!isset($v->clssfx)) {
						$v->clssfx = $v->params->get('pageclass_sfx', '');
					}

					$v->_idx = count($list);
					
					array_push($list, $v);
					$children[$pt] = $list;
					$cacheIndex[$v->id] = $index;
					$this->items[$v->id] = $v;
				}
			}
			$this->children = $children;

			//unset item load module but no content
			foreach ($this->items as $v) {
				if (($v->params->get('subcontent') || $v->params->get('modid') || $v->params->get('modname') || $v->params->get('modpos'))
					&& !isset($this->children[$v->id]) && (!isset($v->content) || !$v->content)
				) {
					$this->remove_item($this->items[$v->id]);
					unset($this->items[$v->id]);
				}
			}
		}

		/**
		 * Remove item menu
		 *
		 * @param object $item  Menu item
		 *
		 * @return void
		 */
		function remove_item($item)
		{
			$result = array();
			foreach ($this->children[$item->parent_id] as $o) {
				if ($o->id != $item->id) {
					$result[] = $o;
				}
			}
			$this->children[$item->parent_id] = $result;
		}

		/**
		 * Parse title
		 *
		 * @param string $title  Title data
		 *
		 * @return JParameter  Menu item title parameter
		 */
		function parseTitle($title)
		{
			//replace escape character
			$title = str_replace(array('\\[', '\\]'), array('%open%', '%close%'), $title);
			$regex = '/([^\[]*)\[([^\]]*)\](.*)$/';
			if (preg_match($regex, $title, $matches)) {
				$title  = $matches[1];
				$params = $matches[2];
				$desc   = $matches[3];
			} else {
				$params = '';
				$desc   = '';
			}
			$title = str_replace(array('%open%', '%close%'), array('[', ']'), $title);
			$desc  = str_replace(array('%open%', '%close%'), array('[', ']'), $desc);

			$result = new JRegistry('');
			$result->set('title', trim($title));
			$result->set('desc', trim($desc));
			if ($params) {
				if (preg_match_all('/([^\s]+)=([^\s]+)/', $params, $matches)) {
					for ($i = 0; $i < count($matches[0]); $i++) {
						$result->set($matches[1][$i], $matches[2][$i]);
					}
				}
			}
			return $result;
		}

		/**
		 * Load modules
		 *
		 * @param JParameter $params  Modules parameter
		 *
		 * @return mixed  null if subcontent isn't exists, otherwise string
		 */
		function loadModules($params)
		{
			//Load module
			$modules = array();
			switch ($params->get('mega_subcontent')) {
				case 'mod':
					$ids = $params->get('mega_subcontent_mod_modules', array());
					foreach ($ids as $id) {
						if ($id && $module = $this->getModule($id)){
							$modules[] = $module;
						}
					}
					return $modules;
					break;
				case 'pos':
					$poses = $params->get('mega_subcontent_pos_positions', array());
					foreach ($poses as $pos) {
						$modules = array_merge($modules, $this->getModules($pos));
					}
					return $modules;
					break;
				default:
					return null; //load as old method
					break;
			}
			return null;
		}

		/**
		 * Get modules by position
		 *
		 * @param string $position  The position of module
		 *
		 * @return array  An array of module object
		 */
		function getModules($position)
		{
			return JModuleHelper::getModules($position);
		}

		/**
		 * Get rendering of module
		 *
		 * @param int	$id	Module id
		 * @param string $name  Module name
		 *
		 * @return string  Result after render a module
		 */
		function getModule($id = 0, $name = '')
		{
			$itemid = $this->itemid;
			$app = JFactory::getApplication();
			$user = JFactory::getUser();
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$lang = JFactory::getLanguage()->getTag();
			$clientid = (int) $app->getClientId();
			$db = JFactory::getDbo();

			$query = $db->getQuery(true);
			$query->select('m.id, m.title, m.module, m.position, m.content, m.showtitle, m.params, mm.menuid');
			$query->from('#__modules AS m');
			$query->join('LEFT', '#__modules_menu AS mm ON mm.moduleid = m.id');
			$query->where('m.published = 1');
			$query->where('m.id = ' . $id);

			$query->join('LEFT', '#__extensions AS e ON e.element = m.module AND e.client_id = m.client_id');
			$query->where('e.enabled = 1');

			$date = JFactory::getDate();
			$now = $date->toSql();
			$nullDate = $db->getNullDate();
			$query->where('(m.publish_up = ' . $db->Quote($nullDate) . ' OR m.publish_up <= ' . $db->Quote($now) . ')');
			$query->where('(m.publish_down = ' . $db->Quote($nullDate) . ' OR m.publish_down >= ' . $db->Quote($now) . ')');

			if (!$user->authorise('core.admin', 1)) {
				$query->where('m.access IN (' . $groups . ')');
			}
			$query->where('m.client_id = ' . $clientid);
			if (isset($itemid)) {
				$query->where('(mm.menuid = ' . (int) $itemid . ' OR mm.menuid <= 0)');
			}
			// Filter by language
			if ($app->isSite() && $app->getLanguageFilter())
			{
				$query->where('m.language IN (' . $db->Quote($lang) . ',' . $db->Quote('*') . ')');
			}
			$query->order('m.position, m.ordering');

			// Set the query
			$db->setQuery($query);
			$module = $db->loadObject();

			if (!$module) return null;

			$negId = $itemid ? -(int) $itemid : false;
			// The module is excluded if there is an explicit prohibition, or if
			// the Itemid is missing or zero and the module is in exclude mode.
			$negHit = ($negId === (int) $module->menuid) || (!$negId && (int) $module->menuid < 0);

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

			return $module;
		}

		/**
		 * Generate menu item
		 *
		 * @param object $item   Menu item
		 * @param int	$level  Level of menu item
		 * @param string $pos	Position of menu item
		 * @param int	$ret	Return or show data
		 *
		 * @return mixed  void if ret = 1, otherwise string data of  menu item generating
		 */
		function genMenuItem($item, $level = 0, $pos = '', $ret = 0)
		{
			$data = '';
			$tmp = $item;
			$params = $item->params;
			$tmpname = ($this->params->get('mm_enable') && !$params->get('showtitle', 1)) ? '' : $tmp->title;
			$anchor_title = $params->get('menu-anchor_title', '');
			if (!$anchor_title){
				$anchor_title = $tmpname;
			}
			
			// Print a link if it exists
			$id = 'id="menu' . $tmp->id . '"';
			$itembg = '';
			
			if ($this->params->get('mm_image') && $params->get('menu_image') && $params->get('menu_image') != -1) {
				if ($this->params->get('mm_as_bg')) {
					$itembg = 'style="background-image:url(' . JURI::base(true) . '/' . $params->get('menu_image') . ');"';
					$txt = '<span class="menu-title">' . $tmpname . '</span>';
				} else {
					$txt = '<span class="menu-image"><img src="' . JURI::base(true) . '/' . $params->get('menu_image') . '" alt="' . $tmpname . '" title="' . $anchor_title . '" /></span><span class="menu-title">' . $tmpname . '</span>';
				}
			} else {
				$txt = '<span class="menu-title">' . $tmpname . '</span>';
			}
			
			$cls = $this->genClass($tmp, $level, $pos);
			
			if (isset($this->children[$tmp->id]) || !empty($tmp->content)) {
				$cls .= ' dropdown-toggle';
				
				if ($level < $this->endlevel && !$item->params->get('mega_group')){
					$txt .= '<b class="caret"></b>';
				}
			}
			
			if ($cls){
				$cls = ' class="' . $cls . '"';
			}
			
			// Add page title to item
			// Get and filter description
			$desc = trim(str_replace('&nbsp;', ' ', $tmp->params->get('mega_desc')));
			if ($desc) {
				$txt .= '<small class="menu-desc">' . JText::_($desc) . '</small>';
			}
			
			if (isset($itembg) && $itembg) {
				$hasClass = ($desc) ? ' has-desc' : '';
				$txt = '<span class="has-image' . $hasClass. '" '. $itembg . '>' . $txt . '</span>';
			}
			$title = 'title="' . $anchor_title . '"';

			if ($tmp->type == 'menulink') {
				// $menu = &JSite::getMenu();
				$menu = JFactory::getApplication()->getMenu();
				$alias_item = clone ($menu->getItem($tmp->query['Itemid']));
				if (!$alias_item) {
					return false;
				} else {
					$tmp->url = $alias_item->link;
				}
			}
			
			if ($tmpname) {
				if ($tmp->type == 'separator') {
					$data = '<a href="#" ' . $cls . ' ' . $id . ' ' . $title . '>' . $txt . '</a>';
				} else {
					if ($tmp->url != null) {
						switch ($tmp->browserNav) {
							default:
							case 0:
								// _top
								$data = '<a href="' . $tmp->url . '" ' . $cls . ' ' . $id . ' ' . $title . '>' . $txt . '</a>';
								break;
							case 1:
								// _blank
								$data = '<a href="' . $tmp->url . '" target="_blank" ' . $cls . ' ' . $id . ' ' . $title . '>' . $txt . '</a>';
								break;
							case 2:
								// window.open
								$attribs = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,' . $this->getParam('window_open');

								// hrm...this is a bit dickey
								$link = str_replace('index.php', 'index2.php', $tmp->url);
								$data = '<a href="' . $link . '" onclick="window.open(this.href,\'targetWindow\',\'' . $attribs . '\');return false;" ' . $cls . ' ' . $id . ' ' . $title . '>' . $txt . '</a>';
								break;
						}
					} else {
						$data = '<a ' . $cls . ' ' . $id . ' ' . $title . '>' . $txt . '</a>';
					}
				}
			}

			//for megamenu
			if ($this->params->get('mm_enable')) {
				//For group
				if ($item->params->get('mega_group') && !empty($data)){
					$data = '<div class="megagroup-title nav-header">' . $data . '</div>';
				}
				
				if (!empty($item->content)) {
					if ($item->params->get('mega_group')) {
						$data .= '<div class="megagroup-content">' . $item->content . '</div>';
					} else {
						$data .= $this->beginMenuItems($item->id, $level + 1, true);
						$data .= $item->content;
						$data .= $this->endMenuItems($item->id, $level + 1, true);
					}
				}
			}

			if ($ret)
				return $data;
			else
				echo $data;
		}

		/**
		 * Set parameter value
		 *
		 * @param string $paramName   Parameter name
		 * @param string $paramValue  Parameter value
		 *
		 * @return void
		 */
		function setParam($paramName, $paramValue)
		{
			return $this->params->set($paramName, $paramValue);
		}

		/**
		 * Echo markup before a menu markup
		 *
		 * @param int $startlevel  Start menu level
		 * @param int $endlevel	End menu level
		 *
		 * @return void
		 */
		function beginMenu()
		{
			echo '<div>';
		}

		/**
		 * Echo markup after a menu markup
		 *
		 * @param int $startlevel  Start menu level
		 * @param int $endlevel	End menu level
		 *
		 * @return void
		 */
		function endMenu($startlevel = 0, $endlevel = 10)
		{
			echo '</div>';
		}

		/**
		 * Echo markup before menu items markup
		 *
		 * @param int $pid	Menu item id
		 * @param int $level  Menu item level
		 *
		 * @return void
		 */
		function beginMenuItems($pid = 0, $level = 0)
		{
			echo '<ul>';
		}

		/**
		 * Echo markup after menu items markup
		 *
		 * @param int $pid	Menu item id
		 * @param int $level  Menu item level
		 *
		 * @return void
		 */
		function endMenuItems($pid = 0, $level = 0)
		{
			echo '</ul>';
		}

		/**
		 * Echo markup before submenu items markup
		 *
		 * @param int	$pid	 Menu id
		 * @param int	$level   Level
		 * @param string $pos	 Position
		 * @param int	$i	   Index
		 * @param string $return  Return or not
		 *
		 * @return void
		 */
		function beginSubMenuItems($pid = 0, $level = 0, $pos = '', $i = 0, $return = false)
		{
			//for megamenu menu
		}

		/**
		 * Echo markup after submenu items markup
		 *
		 * @param int	$pid	 Menu id
		 * @param int	$level   Level
		 * @param string $return  Return or not
		 *
		 * @return void
		 */
		function endSubMenuItems($pid = 0, $level = 0, $return = false)
		{
			//for megamenu menu
		}

		/**
		 * Echo markup before menu item markup
		 *
		 * @param object $mitem  Menu item
		 * @param int	$level  Menu level
		 * @param string $pos	Position
		 *
		 * @return void
		 */
		function beginMenuItem($mitem = null, $level = 0, $pos = '')
		{
			$cls = $this->genClass($mitem, $level, $pos);
			if ($cls) {
				$cls = ' class="' . $cls . '"';
			}
			
			echo '<li' . $cls . '>';
		}

		/**
		 * Echo markup after menu item markup
		 *
		 * @param object $mitem  Menu item
		 * @param int	$level  Menu level
		 * @param string $pos	Position
		 *
		 * @return void
		 */
		function endMenuItem($mitem = null, $level = 0, $pos = '')
		{
			echo '</li>';
		}

		/**
		 * Generate class item
		 *
		 * @param object $mitem  Menu item
		 * @param int	$level  Menu level
		 * @param string $pos	Position
		 *
		 * @return void
		 */
		function genClass($mitem, $level, $pos)
		{
			$active = in_array($mitem->id, $this->open);
			$cls = ($level ? '' : "menu-item{$mitem->_idx}") . ($active ? ' active' : '') . ($pos ? " $pos-item" : '');
			if (@$this->children[$mitem->id] && (!$level || $level < $this->endlevel)) {
				$cls .= ' haschild';
			}
			if ($mitem->params->get('class')) {
				$cls .= ' ' . $mitem->params->get('class');
			}
			return $cls;
		}

		/**
		 * Check having submenu
		 *
		 * @param int $level  Menu level
		 *
		 * @return bool  TRUE if having, otherwise FALSE
		 */
		function hasSubMenu($level)
		{
			$pid = $this->getParentId($level);
			if (!$pid) {
				return false;
			}
			return $this->hasSubItems($pid);
		}

		/**
		 * Check having submenu items
		 *
		 * @param int $id  Menu item id
		 *
		 * @return bool  TRUE if having, otherwise FALSE
		 */
		function hasSubItems($id)
		{
			if (@$this->children[$id]) return true;
			return false;
		}

		/**
		 * Generate menu
		 *
		 * @return string  The generate menu rendering
		 */
		function genMenu()
		{
			$this->beginMenu();
			$pid = $this->getParentId(0);
			if ($pid){
				$this->genMenuItems($pid, 0);
			}

			$this->endMenu();
		}

		/**
		 * Generate menu items
		 *
		 * @param int $pid	Menu item
		 * @param int $level  Menu level
		 *
		 * @return void
		 */
		function genMenuItems($pid, $level, $nolevel = false)
		{
			if (isset($this->children[$pid])) {
				//Detect description. If some items have description, must generate empty description for other items
				$hasDesc = false;
				foreach ($this->children[$pid] as $row) {
					if ($row->params->get('mega_desc')) {
						$hasDesc = true;
						break;
					}
				}
				if ($hasDesc) {
					//Update empty description with a space - &nbsp;
					foreach ($this->children[$pid] as $row) {
						if (!$row->params->get('mega_desc')) {
							$row->params->set('mega_desc', '&nbsp;');
						}
					}
				}
				
				$j = 0;
				$total = count($this->children[$pid]);
				$tmp = $pid && isset($this->items[$pid]) ? $this->items[$pid] : new stdclass();
				$cols = $pid && $this->params->get('mm_enable') && isset($this->items[$pid]) && $this->items[$pid]->params->get('mega_multicol') ? $total : 1;
				
				if($cols > 1){
					$tmp->col = array_fill(0, $cols, 1);
				}else{
					$tmp->col = array_fill(0, 1, $total);
				}
				
				$this->beginMenuItems($pid, $level, false, $nolevel);
				for ($col = 0, $j = 0; $col < $cols && $j < $total; $col++) {
					$pos = ($col == 0) ? 'first' : (($col == $cols - 1) ? 'last' : '');
					//recalculate the colw for this column if the first child is group
					if ($this->children[$pid][$j]->params->get('mega_group')
						&& $this->children[$pid][$j]->params->get('mega_width')
						&& isset($this->items[$pid])
					) {
						$this->items[$pid]->params->set('mega_child_width', $this->children[$pid][$j]->params->get('mega_width'));
					}
					
					$this->beginSubMenuItems($pid, $level, $pos, $col);
					$i = 0;
				
					while ($i < $tmp->col[$col] && $j < $total) {
						$row = $this->children[$pid][$j];
						$pos = ($i == 0) ? 'first' : (($i == count($this->children[$pid]) - 1) ? 'last' : '');

						$this->beginMenuItem($row, $level, $pos);
						$this->genMenuItem($row, $level, $pos);
						// show menu with menu expanded - submenus visible
						if ($this->params->get('mm_enable') && $row->params->get('mega_group')) {
							$this->genMenuItems($row->id, $level); //not increase level
						} elseif ($level < $this->endlevel) {
							$this->genMenuItems($row->id, $level + 1);
						}

						$this->endMenuItem($row, $level, $pos);
						$j++;
						$i++;
					}
					
					$this->endSubMenuItems($pid, $level);
				}
				$this->endMenuItems($pid, $level, false, $nolevel);
			}
		}

		/**
		 * Generate indent text before text
		 *
		 * @param int $level  Menu level
		 * @param int $text   Text data
		 *
		 * @return void
		 */
		function indentText($level, $text)
		{
			echo "\n";
			for ($i = 0; $i < $level; ++$i) {
				echo "   ";
			}
			echo $text;
		}

		/**
		 * Get parent id
		 *
		 * @param int $shiftlevel  Shift level
		 *
		 * @return unknown
		 */
		function getParentId($shiftlevel)
		{
			$level = $shiftlevel - 1;
			if ($level < 0 || (count($this->open) <= $level)) return 1; //out of range
			return $this->open[$level];
		}

		/**
		 * Get parent text
		 *
		 * @param int $level  Level
		 *
		 * @return string
		 */
		function getParentText($level)
		{
			$pid = $this->getParentId($level);
			if ($pid) {
				return $this->items[$pid]->title;
			} else
				return "";
		}

		/**
		 * Generate menu head
		 *
		 * @return void
		 */
		function genMenuHead()
		{
			if (isset($this->_css) && count($this->_css)) {
				foreach ($this->_css as $url) {
					echo "<link href=\"{$url}\" rel=\"stylesheet\" type=\"text/css\" />";
				}
			}
			if (isset($this->_js) && count($this->_js)) {
				foreach ($this->_js as $url) {
					echo "<script src=\"{$url}\" language=\"javascript\" type=\"text/javascript\"></script>";
				}
			}
		}
	}
}