<?php
/**
 * ------------------------------------------------------------------------
 * JA Tabs plugin for Joomla 1.7.x
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

/* Check to ensure this file is included in Joomla! */

defined('_JEXEC') or die();
if(!defined('DS')){
	define('DS', DIRECTORY_SEPARATOR);
}
jimport('joomla.plugin.plugin');
jimport('joomla.application.module.helper');
require_once  JPATH_SITE.'/components/com_content/helpers/route.php';
//jimport('joomla.application.component.model');

/**
 * Jatabs Content Plugin
 *
 * @package		Joomla
 * @subpackage	System
 * @since 		1.7
 */

class plgSystemjatabs extends JPlugin
{
    var $style_default = '';
    var $_plgCode = '#{(.*?)jatabs(.*?)}#i';
    var $_body = NULL;
    var $_styles = NULL;


    /**
     * Constructor
     *
     * For php4 compatability we must not use the __constructor as a constructor for plugins
     * because func_get_args ( void ) returns a copy of all passed arguments, NOT references.
     * This causes problems with cross-referencing necessary for the observer design pattern.
     *
     * @param	object	$subject The object to observe
     * @param	object 	$params  The object to observe
     */
    function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);

    }


    /**
     * tabs prepare content method
     *
     * Method is called by the view
     *
     * @param 	object		The article object.  Note $article->text is also available
     * @param 	object		The article params
     * @param 	int			The 'page' number
     */
    public function onAfterRender()
    {
        $app = JFactory::getApplication();
        if ($app->isAdmin())
            return;

        $document = JFactory::getDocument();
        $styles = $document->_styleSheets;
        $scripts = $document->_scripts;
		$scriptsDeclartionBefore = $document->_script;
		
        $this->_body = JResponse::getBody();
        $disable_tab = $this->params->get('disable_tab', 0);
        if ($disable_tab) {
            $this->_body = $this->removeCode($this->_body);
            JResponse::setBody($this->_body);
            return;
        }

        if (JString::strpos($this->_body, '{jatabs') === false) {
            $HSmethodDIRECT = false;
        } else {
            $HSmethodDIRECT = true;
        }
		$flag = false;
        if ($HSmethodDIRECT) {
            if (!defined("JAMOOTAB_PLUGIN_HEADTAG"))
                $this->stylesheet();

            require_once ('plugins/system/jatabs/jatabs/parser.php');
            $parser = new ReplaceCallbackParserTabs('jatabs');
			$string_jatabs = preg_split('#(.*?)textarea(.*?)#i', $this->_body, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			foreach($string_jatabs as $value_jatabs) {
				if ((JString::strpos($value_jatabs, '&lt;p&gt;{jatabs'))) {
					$string_backup = $value_jatabs;
					$flag = true;
					break;
				}
			}
			if ($flag == true) {
				$this->_body = str_replace($string_backup, "JATABSJOOMLART", $this->_body);
			}
			$this->_body = $parser->parse($this->_body, array(&$this, 'Jatabs_replacer_DIRECT'));

			if ($flag == true) {
				$this->_body = str_replace("JATABSJOOMLART", $string_backup, $this->_body);
			}
			
        }

        if ($this->_styles)
            $this->_body = str_replace('</head>', "\t" . implode(' ', $this->_styles) . "\n</head>", $this->_body);

        $document = JFactory::getDocument();
        $styles_1 = $document->_styleSheets;
        $scripts_1 = $document->_scripts;
		$scriptsDeclartionAfter = $document->_script;
		
        $styles_diff = array_diff_key($styles_1, $styles);
        $scripts_diff = array_diff_key($scripts_1, $scripts);
		$scriptsDeclartionDiff = array_diff($scriptsDeclartionAfter,$scriptsDeclartionBefore );
        if ($styles_diff) {
            foreach ($styles_diff as $strSrc => $strAttr) {
                $headtag = '<link href="' . $strSrc . '" type="text/css" rel="stylesheet" />';
                $this->_body = str_replace('</head>', "\t" . $headtag . "\n</head>", $this->_body);
            }
        }

        if ($scripts_diff) {
            foreach ($scripts_diff as $strSrc => $strType) {
                $headtag = '<script src="' . $strSrc . '" type="text/javascript" ></script>';
                $this->_body = str_replace('</head>', "\t" . $headtag . "\n</head>", $this->_body);
            }
        }
		
		if ($scriptsDeclartionDiff) {
            foreach ($scriptsDeclartionDiff as $strDeclartion) {
                $headtag = '<script type="text/javascript" >'.$strDeclartion.'</script>';
                $this->_body = str_replace('</head>', "\t" . $headtag . "\n</head>", $this->_body);
            }
        }
        JResponse::setBody($this->_body);
    }


    /**
     *
     * Process data when after route
     */
    function onAfterRoute()
    {
        if (JRequest::getCmd('jatabaction')) {
            $action = JRequest::getCmd('jatabaction');
            require_once dirname(__FILE__) . DS . 'jatabs' . DS . 'ajaxloader.php';
            $obj = new JATAB_Ajax();

            if ($action && method_exists($obj, $action)) {
                $buffer = $obj->$action();
                JResponse::setBody($buffer);

                $dispatcher = JDispatcher::getInstance();
                $plugin = JPluginHelper::getPlugin('system', 'sef');

                $plugin = new plgSystemSef($dispatcher, (array) ($plugin));
                $plugin->onAfterRender();

                $result['text'] = JResponse::getBody();
                echo json_encode($result);
                exit();
            }
        }
    }


    /**
     *
     * Remove code tag tabs in content
     * @param string $content
     * @return string
     */
    function removeCode($content)
    {
        return preg_replace($this->_plgCode, '', $content);
    }


    /**
     *
     * Set style for data display
     */
    function stylesheet()
    {
        $assets_url = JURI::root() . 'plugins/system/jatabs/jatabs/';
		JHTML::_('behavior.framework', true);
		JHTML::script($assets_url . 'ja.tabs.js');
        $headtag = array();
        $headtag[] = '<link href="' . $assets_url . 'ja.tabs.css" type="text/css" rel="stylesheet" />';
        //$headtag[] = '<script src="' . $assets_url . 'ja.tabs.js" type="text/javascript" ></script>';
        $this->_body = str_replace('</head>', "\t" . implode("\n", $headtag) . "\n</head>", $this->_body);
        define("JAMOOTAB_PLUGIN_HEADTAG", TRUE);
    }


    /**
     *
     * Control display data type in tabs
     * @param string $plgAttr
     * @param string $plgContent
     * @return string
     */
    function Jatabs_replacer_DIRECT($plgAttr, $plgContent)
    {
        //params of tab
        $params = '';
        $params = $this->parseParams($plgAttr);

        if ( isset($params['ajax']) ) {
            if(intval($params['ajax']) > 0 || trim(strtolower($params['ajax']))=='true'){
                $params['useAjax'] = "true";
            } else {
                //set params for ajax
                $params['useAjax'] = "false";
            }
        }else{
            $params['useAjax'] = "false";
        }
        if (!isset($params['type']))
            return;
        switch ($params['type']) {
            case 'content':
                return $this->parseTabContent($plgContent, $params);
            case 'modules':
                return $this->parseTabModules($params);
            case 'module':
                return $this->parseTabModule($params);
            case 'articles':
                return $this->parseTabArticle($params);
        }
    }


    /**
     *
     * Parse custom content into tab
     * @param array $matches
     * @param object $params
     * @return string
     */
    function parseTabContent($matches, $params)
    {
        $tabs = '';
        $_SESSION['li'] = null;
        $_SESSION['div'] = null;
        $regex = $this->getSubPattern('tab');
        preg_replace_callback($regex, array(&$this, 'wirentTabContent'), $matches);
        if ($_SESSION['li'] != null) {
            return $this->writeTabs($_SESSION['li'], $_SESSION['div'], $params);
        }
        return '';
    }


    /**
     *
     * Callback function for custom content tab
     * @param array $matches
     * @return string
     */
    function wirentTabContent(&$matches)
    {
        $params = $this->parseParams($matches[1]);
        //Add class option for each tab
        $jaclass = (isset($params['color'])) ? " class=\"{$params['color']}\"" : "";
        $_SESSION['li'] .= "<li><h3$jaclass><span>" . JText::_($params['title']) . "</span></h3></li>";

        $_SESSION['div'] .= "<div class=\"ja-tab-content\">
								<div class=\"ja-tab-subcontent\">" . $matches[2] . '  </div>
							</div>';
    }


    /**
     *
     * Parse modules content into tab
     * @param object $params
     * @return string
     */
    function parseTabModules($params)
    {
        $module_content = '';
        $lis = '';
        $divs = '';
        $list = JModuleHelper::getModules(trim($params['module']));
        $ids = array();
        $params['numbertabs'] = 0;
        for ($j = 0; $j < count($list); $j++) {
            if ($list[$j]->module != 'mod_jatabs') {
                $mparams = new JRegistry();
                $mparams->loadString($list[$j]->params);
                $clssfx = $mparams->get('moduleclass_sfx', '');
                $lis .= "<li title=\"" . strip_tags($list[$j]->title) . "\"" . ($clssfx ? " class=\"ja-tab-title$clssfx\"" : "") . "><h3><span>" . JText::_($list[$j]->title) . "</span></h3></li>";
                $divs .= "<div  class=\"ja-tab-content" . ($clssfx ? " ja-tab-content$clssfx" : "") . "\">
							<div class=\"ja-tab-subcontent\">";
                if ($params['useAjax'] == 'false') {
                    $divs .= JModuleHelper::renderModule($list[$j]);
                }
                $divs .= '  </div>
						 </div>';

                $ids[] = $list[$j]->id;
                $params['numbertabs']++;
            }
        }

        if ($params['useAjax'] == 'true') {
            $params['ajaxUrl'] = JRoute::_('index.php' . '?jatabaction=modules');
        }

        if ($lis != '') {

            $params['ids'] = implode(',', $ids);

            return $this->writeTabs($lis, $divs, $params);
        }

        return;
    }


    /**
     *
     * Parse module content into tab
     * @param object $params
     * @return string
     */
    function parseTabModule($params)
    {
        $lis = '';
        $divs = '';
        $list_module = array();
        if (isset($params['modulename']) && $params['modulename']){
            $list_module = explode(",", $params['modulename']);
        }
        $ids = array();
        $params['numbertabs'] = 0;

        for ($i = 0; $i < count($list_module); $i++) {
            if ($list_module[$i] != 'mod_jatabs') {
                $module = JModuleHelper::getModule(substr(trim($list_module[$i]), 4));

                if ($module && $module->id) {
                    $mparams = new JRegistry();
                    $mparams->loadString($module->params);
                    $clssfx = $mparams->get('moduleclass_sfx', '');
                    $lis .= "<li title=\"" . strip_tags($module->title) . "\"" . ($clssfx ? " class=\"ja-tab-title$clssfx\"" : "") . "><h3><span>" . JText::_($module->title) . "</span></h3></li>";
                    $divs .= "<div  class=\"ja-tab-content" . ($clssfx ? " ja-tab-content$clssfx" : "") . "\">
								<div class=\"ja-tab-subcontent\">";
                    if ($params['useAjax'] == 'false') {
                        $divs .= JModuleHelper::renderModule($module);
                    }

                    $divs .= '  </div>
							 </div>';
                    $ids[] = $module->id;
                    $params['numbertabs']++;
                }
            }

        }
        if ($lis != '') {

            $params['ids'] = implode(',', $ids);

            if ($params['useAjax'] == 'true') {
                $params['ajaxUrl'] = JRoute::_('index.php' . '?jatabaction=modules');
            }

            return $this->writeTabs($lis, $divs, $params);
        }
        return '';
    }


    /**
     *
     * Parse article content into tab
     * @param object $params
     * @return string
     */
    function parseTabArticle($params)
    {
        $list = null;
        $lis = '';
        $divs = '';
        if (isset($params['ids'])) {
            $list = $this->getList($params['ids'], '', 0);
        } elseif (isset($params['catid'])) {
            if (!isset($params['maxitems']) || $params['maxitems'] <= 0 || !is_numeric($params['maxitems']))
                $params['maxitems'] = 0;
            $list = $this->getList('', $params['catid'], $params['maxitems']);
        }

        $params['numbertabs'] = 0;

        $ids = array();
        if ($list) {
            foreach ($list as $row) {
                $ids[] = $row->id;
                $title = $row->title;

                if (JText::_($row->alias) != $row->alias)
                    $title = JText::_($row->alias);

                $lis .= "<li title=\"" . strip_tags($row->title) . "\"><h3><span>" . $title . "</span></h3></li>";
                $divs .= "<div  class=\"ja-tab-content\">
							<div class=\"ja-tab-subcontent\">";

                $params['readmore'] = isset($params['readmore']) ? $params['readmore'] : 1;

                if ($params['useAjax'] == 'false') {
                    if ($params['view'] == 'fulltext') {
                        $divs .= JHtml::_('content.prepare', $row->introtext . $row->fulltext);
                    } else {
                        $divs .= JHtml::_('content.prepare', $row->introtext);
                    }

                    if ($params['readmore'] == 1) {
                        $divs .= "<br/><a class='readmore' href='$row->link' title='" . JText::_('Read more') . "'>" . JText::_('Read more') . "</a>";
                        ;
                    }
                }
                $divs .= '  </div>
						 </div>';

                $params['numbertabs']++;
            }

            if ($params['useAjax'] == 'true') {
                $params['ajaxUrl'] = JRoute::_('?jatabaction=content&view=' . $params['view']);
            }

            $params['ids'] = implode(',', $ids);

            return $this->writeTabs($lis, $divs, $params);
        }

        return '';
    }


    /**
     *
     * Display tab
     * @param string $lis title tab
     * @param string $divs content tab
     * @param array $params
     * @return string
     */
    function writeTabs($lis, $divs, $params)
    {
        $padding = '';
        $override = '';
        $k = '';

        if ($params['position'] == 'left' && is_numeric($params['widthTabs'])) {
            $width = $params['width'] - $params['widthTabs'];
        }

        if ($params['position'] == 'right' && is_numeric($params['widthTabs'])) {
            $padding = 'left:' . ($params['widthTabs'] + 5) . 'px;';
            $width = $params['width'] - $params['widthTabs'];
        }

        foreach ($params as $k => $value) {
            if ($k != 'type' && $k != 'module' && $k != 'modulename' && $k != 'widthTabs' && $k != 'heightTabs' && $k != 'view' && $k != 'ajax') {
                if (is_numeric($value) || $k == 'skipAnim' || $k == 'useAjax') {
                    $override .= $k . ":" . $value . ",";
                } else
                    $override .= $k . ":'" . $value . "',";
            }
        }

        if ($override != '') {
            $override = substr($override, 0, strlen($override) - 1);
        }

        $html = '';

        if (!defined("JAMOOTAB_HEADTAG_" . strtoupper($params['style']))) {
            $this->_styles[] = '<link href="' . JURI::root() . 'plugins/system/jatabs/jatabs/themes/' . $params['style'] . '/style.css" type="text/css" rel="stylesheet" />';
            define("JAMOOTAB_HEADTAG_" . strtoupper($params['style']), true);
        }

        $id = 'myTab-' . rand();
        $idtab = rand();

        $style = 'style="';
        if (is_numeric($params['height']) && $params['height'] > 0) {
            $style .= 'height:' . $params['height'] . 'px;';
        }
        if (is_numeric($params['width']) && $params['width'] > 0) {
            $style .= 'width:' . $params['width'] . 'px;';
        } else {
            $style .= 'width:100%;';
        }
        $style .= '"';

        $html .= '<div class="ja-tabswrap ' . strtolower($params['style']) . '" ' . $style . '>';

        $html .= '	<div  id="' . $id . '" class="container" >';

        if ($params['position'] == 'top') {
            /* set style for title top */
            $styleTop = 'style="';
            if (is_numeric($params['heightTabs']) && $params['heightTabs'] > 0) {
                $styleTop .= 'height:' . $params['heightTabs'] . 'px;';
            }
            $styleTop .= '"';

            $html .= '	<div class="ja-tabs-title-' . $params['position'] . '" ' . $styleTop . '>';
        } elseif ($params['position'] != 'bottom') {
            /* set style for title top */
            $styleMiddle = 'style="';

            if (is_numeric($params['widthTabs']) && $params['widthTabs'] > 0) {
                $styleMiddle .= 'width:' . $params['widthTabs'] . 'px;';
            } else {
                $styleMiddle .= 'width:' . $params['widthTabs'] . ';';
            }
            $styleMiddle .= '"';

            $html .= '	<div class="ja-tabs-title-' . $params['position'] . '" ' . $styleMiddle . '>';
        }

        $style = '';
        if (is_numeric($params['height']) && $params['height'] > 0) {
            $style = 'style="height:0px;"';
        }

        if (!isset($params['numbertabs']))
            $params['numbertabs'] = 1;
        $numbertab = JRequest::getInt('tab', 0);
        if ($numbertab < 0)
            $numbertab = 0;
        if ($numbertab >= $params['numbertabs'])
            $numbertab = $params['numbertabs'] - 1;

        if ($params['position'] == 'bottom') {
            $html .= '<div class="ja-tab-panels-' . $params['position'] . '" ' . $style . '>' . $divs . '</div>
						<div class="ja-tabs-title-' . $params['position'] . '" >
							<ul class="ja-tabs-title">' . $lis . '</ul>
						 </div>
						';
        } else {
            $html .= '
							<ul class="ja-tabs-title">' . $lis . '</ul>
						</div>
						<div class="ja-tab-panels-' . $params['position'] . '" ' . $style . '>' . $divs . '</div>';
        }

        $html .= '	</div>
				</div>';
        $html .= '<script type="text/javascript" charset="utf-8">
					window.addEvent("load", function(){
						new JATabs("' . $id . '", {' . $override . ', siteroot:\'' . JURI::root() . '\', numbtab: ' . $numbertab . '});
					});
			     </script>';
        return $html;
    }


    /**
     *
     * Get list article
     * @param string $ids
     * @param string $catid
     * @param int $limit
     * @return array
     */
    public static function getList($ids = '', $catid = '', $limit = 0)
    {
	    $app = JFactory::getApplication();
		if (!$app->isAdmin()){
			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_content/models', 'ContentModel');
				//$model = JModelLegacy::getInstance('Article', 'ContentModel', array('ignore_request' => true));
			}
			else if (version_compare(JVERSION, '2.5', 'ge'))
			{
				JModel::addIncludePath(JPATH_SITE.'/components/com_content/models', 'ContentModel');
			   	//$model = JModel::getInstance('Article', 'ContentModel', array('ignore_request' => true));
			}
			else
			{
				JModel::addIncludePath(JPATH_SITE.'/components/com_content/models', 'ContentModel');
				//$model = JModel::getInstance('Article', 'ContentModel', array('ignore_request' => true));
			}
		  
		}
    	// Get the dbo
        $db = JFactory::getDbo();
		
        // Get an instance of the generic articles model
        
	    if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$model = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
			//$model = JModelLegacy::getInstance('Article', 'ContentModel', array('ignore_request' => true));
		}
		else if (version_compare(JVERSION, '2.5', 'ge'))
		{
			$model = JModel::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
		   	//$model = JModel::getInstance('Article', 'ContentModel', array('ignore_request' => true));
		}
		else
		{
			$model = JModel::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
			//$model = JModel::getInstance('Article', 'ContentModel', array('ignore_request' => true));
		}
        // Set application parameters in model
        $appParams = JFactory::getApplication()->getParams();
        $model->setState('params', $appParams);

        // Set the filters based on the module params
        $model->setState('list.start', 0);
        if ($catid != '' && $limit > 0) {
            $model->setState('list.limit', $limit);
        }

        $model->setState('filter.published', 1);
        $model->setState('list.select', 'a.id, a.title, a.alias, a.introtext, a.fulltext, a.state, a.catid, a.created, a.created_by, a.created_by_alias,' . // use created if modified is 0
'CASE WHEN a.modified = 0 THEN a.created ELSE a.modified END as modified,' . 'a.modified_by, uam.name as modified_by_name,' . // use created if publish_up is 0
'CASE WHEN a.publish_up = 0 THEN a.created ELSE a.publish_up END as publish_up,' . 'a.publish_down, a.attribs, a.metadata, a.metakey, a.metadesc, a.access,' . 'a.hits, a.xreference, a.featured,' . ' LENGTH(a.fulltext) AS readmore');

        // Access filter
        $access = !JComponentHelper::getParams('com_content')->get('show_noauth');
        $authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
        $model->setState('filter.access', $access);

        // Category filter
        if ($catid) {
            $model->setState('filter.category_id', explode(',', $catid));
        }
        if ($ids) {
            $model->setState('filter.article_id', explode(',', $ids));
        }

        // Set ordering
        $model->setState('list.ordering', 'a.ordering');
        $model->setState('list.direction', '');

        $items = $model->getItems();

        if (count($items)) {
            foreach ($items as &$item) {
                $item->slug = $item->id . ':' . $item->alias;
                $item->catslug = $item->catid;

                if ($access || in_array($item->access, $authorised)) {
                    // We know that user has the privilege to view the article
                    $item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
                } else {
                    $item->link = JRoute::_('index.php?option=com_user&view=login');
                }

                $item->introtext = JHtml::_('content.prepare', $item->introtext);
                $item->fulltext = JHtml::_('content.prepare', $item->fulltext);
            }
        }

        return $items;
    }


    /**
     *
     * Render pattern
     * @param string $tag
     * @return string
     */
    function getPattern($tag)
    {
        $regex = '#{' . $tag . ' ([^}]*)}([^{]*){/' . $tag . '}#m';

        return $regex;
    }


    /**
     *
     * Render sub pattern
     * @param string $tag
     * @return string
     */
    function getSubPattern($tag)
    {
        $regex = '#\[' . $tag . ' ([^\]]*)\]([^\[]*)\[/' . $tag . '\]#m';
        return $regex;
    }


    /**
     *
     * Parse params to array
     * @param string $params
     * @return array
     */
    function parseParams($params)
    {
        $params = html_entity_decode($params, ENT_QUOTES);
        $regex = "/\s*([^=\s]+)\s*=\s*('([^']*)'|\"([^\"]*)\"|([^\s]*))/";
        preg_match_all($regex, $params, $matches);

        $paramarray = null;
        if (count($matches)) {
            $paramarray = array();
            for ($i = 0; $i < count($matches[1]); $i++) {
                $key = $matches[1][$i];
                $val = $matches[3][$i] ? $matches[3][$i] : ($matches[4][$i] ? $matches[4][$i] : $matches[5][$i]);
                $paramarray[$key] = $val;
            }
        }
        if (!isset($paramarray['style']) || !$paramarray['style'])
            $paramarray['style'] = $this->params->def('style', 'default');
        if (!isset($paramarray['position']) || !in_array(strtolower($paramarray['position']), array('top', 'bottom', 'left', 'right')))
            $paramarray['position'] = $this->params->def('position', 'top');
        if (!isset($paramarray['width']) || (!is_numeric($paramarray['width']) && $paramarray['width'] != '100%'))
            $paramarray['width'] = $this->params->def('width', '100%');
        if (!isset($paramarray['height']) || (!is_numeric($paramarray['height']) && $paramarray['height'] != 'auto'))
            $paramarray['height'] = $this->params->def('height', 'auto');
        if (!isset($paramarray['widthTabs']) && ($paramarray['position'] == 'left' || $paramarray['position'] == 'right'))
            $paramarray['widthTabs'] = $this->params->def('widthTabs', '150');
        if (!isset($paramarray['heightTabs']))
            $paramarray['heightTabs'] = $this->params->def('heightTabs', '30');
        if (!isset($paramarray['duration']))
            $paramarray['duration'] = $this->params->def('duration', '1000');
        if (!isset($paramarray['animType']))
            $paramarray['animType'] = $this->params->def('animType', 'animMoveVir');
        if (!isset($paramarray['skipAnim']) || !in_array($paramarray['skipAnim'], array('false', 'true')))
            $paramarray['skipAnim'] = $this->params->def('skipAnim', 'false');
        if (!isset($paramarray['view']) || !in_array($paramarray['view'], array('introtext', 'fulltext')))
            $paramarray['view'] = $this->params->def('view', 'introtext');
        if (!isset($paramarray['mouseType']) || !in_array(strtolower($paramarray['mouseType']), array('click', 'mouseover')))
            $paramarray['mouseType'] = $this->params->def('mouseType', 'click');
        if (!isset($paramarray['jaclass']))
            $paramarray['jaclass'] = $this->params->def('jaclass', '');
        if (!isset($paramarray['maxitems']))
            $paramarray['maxitems'] = $this->params->def('maxitems', 0);

        $paramarray['skipAnim'] = strtolower($paramarray['skipAnim']);
        $paramarray['position'] = strtolower($paramarray['position']);
        $paramarray['mouseType'] = strtolower($paramarray['mouseType']);
        if ($paramarray['position'] == 'top' || $paramarray['position'] == 'bottom') {
            $paramarray['widthTabs'] = $paramarray['width'];
        }

        return $paramarray;
    }

}
?>