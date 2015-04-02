<?php
// no direct access
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 

/**
 * ------------------------------------------------------------------------
 * JA Content Popup Module for J25 & J31
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

if (!class_exists('JAK2Helper')) {
    class JAK2Helper
    {
        /**
         *
         * Get Articles of K2
         * @param object $helper
         * @param object $params
         * @return object
         */
        function getList($params,&$helper,$total=null)
        {
            if (! file_exists(JPATH_SITE . '/components/com_k2/helpers/route.php')) {
				return ;
			}
            require_once (JPATH_SITE . '/components/com_k2/helpers/route.php');

            $helper->set('getChildren', $params->get('getChildren', 1));
            $catsid = $params->get('k2catsid');
            $catids = array();
            if (!is_array($catsid)) {
                $catids[] = $catsid;
            } else {
                $catids = $catsid;
            }

            JArrayHelper::toInteger($catids);
            if ($catids) {
                if ($catids && count($catids) > 0) {
                    foreach ($catids as $k => $catid) {
                        if (!$catid)
                            unset($catids[$k]);
                    }
                }
            }

            jimport('joomla.filesystem.file');
            $limit = (int) $helper->get('limit', 10);
            if (!$limit)
                $limit = 4;
            $limitstart = (int) $helper->get('limitstart', 0);
            $ordering = $helper->get('ordering', '');
            $sort_order = $helper->get('sort_order','DESC');
            $componentParams = JComponentHelper::getParams('com_k2');

            $user = JFactory::getUser();
            $aid = $user->get('aid') ? $user->get('aid') : 1;
            $db = JFactory::getDBO();

            $jnow = JFactory::getDate();
            //$now = $jnow->toMySQL();
	        if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$now = $jnow->toSql();
				
			}
			else if (version_compare(JVERSION, '2.5', 'ge'))
			{
				$now = $jnow->toMySQL();
				
			}
			else
			{
				$now = $jnow->toMySQL();
				
			}
            $nullDate = $db->getNullDate();

			$query 	= "SELECT i.*, c.name AS categoryname,c.id AS categoryid, c.alias AS categoryalias, c.name as cattitle, c.params AS categoryparams";
            $query .= "\n FROM #__k2_items as i LEFT JOIN #__k2_categories c ON c.id = i.catid";
            $query .= "\n WHERE i.published = 1 AND i.featured=1 AND i.access <= {$aid} AND i.trash = 0 AND c.published = 1 AND c.access <= {$aid} AND c.trash = 0";
            $query .= "\n AND ( i.publish_up = " . $db->Quote($nullDate) . " OR i.publish_up <= " . $db->Quote($now) . " )";
            $query .= "\n AND ( i.publish_down = " . $db->Quote($nullDate) . " OR i.publish_down >= " . $db->Quote($now) . " )";
			
			switch ($params->get('featured')) {
				case 'hide': 
					$query = str_replace("AND i.featured=1", "AND i.featured=0", $query);
					break;
					
				case 'show': 
					$query = str_replace("AND i.featured=1", " ", $query);
					break;
			}
			
            if ($catids) {
                $catids_new = $catids;
                if ($params->get('getChildren', 1)) {
                    foreach ($catids as $k => $catid) {
                        $subcatids = JAK2Helper::getK2CategoryChildren($catid, true);
                        if ($subcatids) {
                            $catids_new = array_merge($catids_new, array_diff($subcatids, $catids_new));
                        }
                    }
                }
                $catids = implode(',', $catids_new);
                $query .= "\n AND i.catid IN ($catids)";
            }

            switch ($ordering) {
                case 'ordering':
                    $ordering = 'featured_ordering';
                    break;

                case 'rorder':
                    $ordering = 'featured_ordering DESC';
                    break;

                case 'rand':
                    $ordering = 'RAND()';
                    break;
            }
			
            if ($ordering == 'RAND()')
                $query .= "\n ORDER BY " . $ordering.' '.$sort_order;
            else
                $query .= "\n ORDER BY i." . $ordering .' '.$sort_order.", i.id desc";
            $db->setQuery($query, $limitstart, $limit);
            $rows = $db->loadObjectList();

            if (count($rows)) {

                foreach ($rows as $j => $row) {

                    //Clean title
                    $row->title = JFilterOutput::ampReplace($row->title);

                    // Introtext
                    $row->text = $row->introtext;

                    //Read more link
                    $row->link = urldecode(JRoute::_(K2HelperRoute::getItemRoute($row->id . ':' . urlencode($row->alias), $row->catid . ':' . urlencode($row->categoryalias))));

                

                    //Images
                    $image = '';
                    if (JFile::exists(JPATH_SITE . '/media/k2/items/cache/' . md5("Image" . $row->id) . '_XL.jpg'))
                        $image = JURI::root() . 'media/k2/items/cache/' . md5("Image" . $row->id) . '_XL.jpg';

                    elseif (JFile::exists(JPATH_SITE . '/media/k2/items/cache/' . md5("Image" . $row->id) . '_XS.jpg'))
                        $image = JURI::root() . 'media/k2/items/cache/' . md5("Image" . $row->id) . '_XS.jpg';

                    elseif (JFile::exists(JPATH_SITE . '/media/k2/items/cache/' . md5("Image" . $row->id) . '_L.jpg'))
                        $image = JURI::root() . 'media/k2/items/cache/' . md5("Image" . $row->id) . '_L.jpg';

                    elseif (JFile::exists(JPATH_SITE . '/media/k2/items/cache/' . md5("Image" . $row->id) . '_S.jpg'))
                        $image = JURI::root() . 'media/k2/items/cache/' . md5("Image" . $row->id) . '_S.jpg';

                    elseif (JFile::exists(JPATH_SITE . '/media/k2/items/cache/' . md5("Image" . $row->id) . '_M.jpg'))
                        $image = JURI::root() . 'media/k2/items/cache/' . md5("Image" . $row->id) . '_M.jpg';

                    elseif (JFile::exists(JPATH_SITE . '/media/k2/items/cache/' . md5("Image" . $row->id) . '_Generic.jpg'))
                        $image = JURI::root() . 'media/k2/items/cache/' . md5("Image" . $row->id) . '_Generic.jpg';

                    if ($image != '') {
                        $row->image = $helper->replaceImage($image);
                    } else {
                         $row->image = $helper->replaceImage($row);		
                    }
                    
					$row->text = $helper->trimString($row->introtext,$params->get('limittext',60),"<img />");
					
					
					if(isset($row->image) && $row->image != ''){
                	if(!$total){
	                	if($j >= $jalimitstart && $j <$limited+$jalimitstart){     
	                		$rows[$i] = $row;
	                	}else {
	                		unset($rows[$i]);
	                	}
	                	$j++;
	                	}else {
	                		$rows[$i] = $row;
	                	}
	                }else {
	                	unset($rows[$i]);
	                }
                    $rows[$j] = $row;
                }
            }
			
            return $rows;
        }


        /**
         *
         * Get K2 category children
         * @param int $catid
         * @param boolean $clear if true return array which is removed value construction
         * @return array
         */
        function getK2CategoryChildren($catid, $clear = false) {

    		static $array = array();
    		if ($clear)
    		$array = array();
    		$user = JFactory::getUser();
    		$aid = $user->get('aid') ? $user->get('aid') : 1;
    		$catid = (int) $catid;
    		$db = JFactory::getDBO();
    		$query = "SELECT * FROM #__k2_categories WHERE parent={$catid} AND published=1 AND trash=0 AND access<={$aid} ORDER BY ordering ";
    		$db->setQuery($query);
    		$rows = $db->loadObjectList();

    		foreach ($rows as $row) {
    			array_push($array, $row->id);
    			if (JAK2Helper::hasK2Children($row->id)) {
    				JAK2Helper::getK2CategoryChildren($row->id);
    			}
    		}
    		return $array;
    	}


    	/**
    	 *
    	 * Check category has children
    	 * @param int $id
    	 * @return boolean
    	 */
    	function hasK2Children($id) {

    		$user = JFactory::getUser();
    		$aid = $user->get('aid') ? $user->get('aid') : 1;
    		$id = (int) $id;
    		$db = JFactory::getDBO();
    		$query = "SELECT * FROM #__k2_categories WHERE parent={$id} AND published=1 AND trash=0 AND access<={$aid} ";
    		$db->setQuery($query);
    		$rows = $db->loadObjectList();

    		if (count($rows)) {
    			return true;
    		} else {
    			return false;
    		}
    	}
        
		function get_categories($params,$helper){
			
			return $rows;
		}
        
    }
}

?>