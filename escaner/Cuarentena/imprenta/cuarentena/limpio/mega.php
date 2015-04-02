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

if (!defined('_JA_MEGA_MENU_CLASS')) {
	define('_JA_MEGA_MENU_CLASS', 1);
	include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'base.php';

	/**
	 * JAMenuMega class
	 *
	 * @package JAT3.Core.Menu
	 */
	class JAMenuMega extends JAMenuBase
	{
		/**
		 * Constructor
		 *
		 * @param array &$params  An array parameter
		 *
		 * @return void
		 */
		function __construct(&$params)
		{
			$params->set('mm_enable', 1);
			parent::__construct($params);
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
			$doc = JFactory::getDocument();
			$doc->addStyleSheet(T3V3_URL . '/css/menu.css');
			$doc->addScript(T3V3_URL . '/js/mega.js');
			
			echo '<div class="nav-mega" id="mega-menu">';
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
			$animation = $this->params->get('mm_anim', 'none');
            $duration = (int)$this->params->get('mm_anim_duration', 300);
            $slide = strpos($animation, 'slide') !== false;
            $fade = strpos($animation, 'fade') !== false;
			
			echo 
				'</div>',
				'<script type="text/javascript">',
					'jQuery(\'#mega-menu\').megamenu({
						slide: ', (int)$slide, ',
						fade: ', (int)$fade , ',
						duration: ', $duration , '
					});',
				'</script>';
		}

		/**
		 * Echo markup before menu items markup
		 *
		 * @param int  $pid	 Menu item id
		 * @param int  $level   Menu item level
		 * @param bool $return  Return or not
		 *
		 * @return mixed  Markup if return = true, otherwise VOID
		 */
		function beginMenuItems($pid = 0, $level = 0, $return = false)
		{
			if ($level) {
				$data = '';
				
				if ($this->items[$pid]->params->get('mega_group')) {
					$cols = $pid && isset($this->items[$pid]->cols) && $this->items[$pid]->cols ? $this->items[$pid]->cols : 1;
					$cols_cls = ($cols > 1) ? " cols$cols" : '';
					$data = "<div class=\"group-content$cols_cls\">";
				} else {
					$width = 2;	//default is span2
					$multicol = false;
					
					
					if ($pid) {
						$item = $this->items[$pid];
						$width = $item->params->get('mega_width', 2);
						$multicol = $item->params->get('mega_multicol', false);				
					}
					
					$data = '<div class="mega-menu"><div class="anim-menu"><div class="row"><div class="span' . $width . '">';
					if($multicol){
						$data .= '<div class="row">';
					}
				}
				
				if ($return)
					return $data;
				else
					echo $data;
			}
		}

		/**
		 * Echo markup after menu items markup
		 *
		 * @param int  $pid	 Menu item id
		 * @param int  $level   Menu item level
		 * @param bool $return  Return or not
		 *
		 * @return mixed  Markup if return = true, otherwise VOID
		 */
		function endMenuItems($pid = 0, $level = 0, $return = false)
		{
			if ($level) {
				$data = '';
				
				if ($this->items[$pid]->params->get('mega_group')) {
					$data = '</div>';
				} else {
					$data = '</div></div></div></div>';
			
					if ($pid) {
						$item = $this->items[$pid];
						$multicol = $item->params->get('mega_multicol', false);
						
						if($multicol){
							$data .= '</div>';
						}
					}
				}
				
				if ($return)
					return $data;
				else
					echo $data;
			}
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
		 * @return mixed  Markup if return = true, otherwise VOID
		 */
		function beginSubMenuItems($pid = 0, $level = 0, $pos = null, $i = 0, $return = false)
		{
			$data = '';
			$level = $level;
			
			if ($level && isset($this->items[$pid])) {
                $item = $this->items[$pid];
				$multicol = $item->params->get('mega_multicol', false);
				
				if($multicol){
					$width = $item->params->get('mega_child_width', 0);
					if($width == 0){
						$width = $item->params->get('mega_width', 2);
					}
					
				    $data .= '<div class="span' . $width . '">';
                }
            }
			
			if ($this->children[$pid]){
				$data .= '<ul' . ($level == 0 ? ' class="nav"' : '') . '>';
			}
			
			if ($return)
				return $data;
			else
				echo $data;
		}

		/**
		 * Echo markup after submenu items markup
		 *
		 * @param int	$pid	 Menu id
		 * @param int	$level   Level
		 * @param string $return  Return or not
		 *
		 * @return mixed  Markup if return = true, otherwise VOID
		 */
		function endSubMenuItems($pid = 0, $level = 0, $return = false)
		{
			$data = '';
			if ($this->children[$pid]){
				$data .= '</ul>';
			}
			
			if ($level && isset($this->items[$pid])) {
                $item = $this->items[$pid];
				$multicol = $item->params->get('mega_multicol', false);
				
				if($multicol){
				    $data .= '</div>';
                }
            }
			
			if ($return)
				return $data;
			else
				echo $data;
		}

		/**
		 * Echo markup before submenu modules markup
		 *
		 * @param object $item	Menu item
		 * @param int	$level   Level
		 * @param string $pos	 Position
		 * @param int	$i	   Index
		 * @param bool   $return  Return or not
		 *
		 * @return mixed  Markup if return = true, otherwise VOID
		 */
		function beginSubMenuModules($item, $level = 0, $pos = null, $i = 0, $return = false)
		{
			$data = '';
			
			if ($level) {
				if ($item->params->get('mega_group')) {
				} else {
					$data .= '<div class="megacol mega-module">';
				}
			}
			
			if ($return)
				return $data;
			else
				echo $data;
		}

		/**
		 * Echo markup after submenu modules markup
		 *
		 * @param object $item	Menu item
		 * @param int	$level   Level
		 * @param bool   $return  Return or not
		 *
		 * @return mixed  Markup if return = true, otherwise FALSE
		 */
		function endSubMenuModules($item, $level = 0, $return = false)
		{
			$data = '';
			if ($level) {
				if ($item->params->get('mega_group')) {
				} else {
					$data .= '</div>';
				}
			}
			if ($return)
				return $data;
			else
				echo $data;
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
			$cls = $pos? $pos : '';
			if (isset($this->children[$mitem->id]) || (isset($mitem->content) && $mitem->content)) {
				if ($mitem->params->get('mega_group')){ //zgroup
					$cls .= ' group';
				} else if ($level < $this->endlevel){
					$cls .= ' dropdown';
				}
			}

			if (strpos($cls, 'group') === false && in_array($mitem->id, $this->open)){ //only add active if not in a group
				$cls .= ' active';	
			}
			
			if ($mitem->params->get('mega_exclass')){
				$cls .= ' ' . $mitem->params->get('mega_exclass');
			}
			return $cls;
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
			echo '<li' . ($cls ? (' class="' . trim($cls) . '"') : '') .'>';
			if ($mitem->params->get('mega_group')){
				echo '<div class="group">';
			}
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
			if ($mitem->params->get('mega_group')){
				echo '</div>';
			}
			
			echo '</li>';
		}
	}
}