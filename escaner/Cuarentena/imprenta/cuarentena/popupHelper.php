<?php
/**
 * ------------------------------------------------------------------------
 * JA Popup Plugin for J25 & J30
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

// Prevent direct access
defined('_JEXEC') or die('Restricted access');
/**
 *
 * JA POPUP HELPER CLASS
 * @author JoomlArt
 *
 */
class JAPopupHelper extends JObject
{
    // Modal name
    var $_modal_name;

    // Plugin params
    var $_pluginParams;

    // Param in {japopup} tag
    var $_tagParams;


    /**
     * Constructor
     *
     * For PHP 5 compatability we must not use the __constructor as a constructor for plugins
     * because func_get_args ( void ) returns a copy of all passed arguments, NOT references.
     * This causes problems with cross-referencing necessary for the observer design pattern.
     *
     * @param	object	$modalName The object to observe
     * @param	object 	$pluginParams  The object to observe
     */
    function __construct($modalName, $pluginParams)
    {
        $this->_modal_name = $modalName;
        $this->_pluginParams = $pluginParams;
    }


    /**
     * Get paramater value accross key
     * @param string $key Key of array
     * @return string Value of key.
     * */
    function getValue($key, $defaultValue = "")
    {
        if (isset($this->_tagParams[$key])) {
            return $this->_tagParams[$key];
        } else {
            return $defaultValue;
        }
    }


    /**
     *
     * Return common value in {japopup} tag and config file
     * @param array $arrJAPopupTag
     * @param string $innerContent
     * @return array
     */
    function getCommonValue($arrJAPopupTag, $innerContent)
    {
        $this->_tagParams = $arrJAPopupTag;
        // Content
        $modalContent = $this->getValue("content", "");
        $modalContent = str_replace("&amp;", "&", $modalContent);
        $modalContent = str_replace("&", "&amp;", $modalContent);
        // Title
        $modalTitle = $this->_pluginParams->get("add_title", "1");
        $modalTitle = ($modalTitle != "0") ? $this->getValue("title") : "";
        // Description
        $modalDesc = $this->_pluginParams->get("add_desc", "1");
        $modalDesc = ($modalDesc != "0") ? $this->getValue("description") : "";
        // Slide show
        $imageNumber = $this->getValue("show", "");
        if ($imageNumber == "")
            $imageNumber = $this->_pluginParams->get("image_slideshow", "one");

        $arrData = array("frameWidth" => $this->getValue("width", 600), "frameHeight" => $this->getValue("height", 400), "content" => html_entity_decode($innerContent), "title" => $modalTitle, "desc" => $modalDesc, "href" => $modalContent, "onclose" => $this->getValue("onclose", ""), "onopen" => $this->getValue("onopen", ""), "imageNumber" => $imageNumber);

        if ($this->getValue("type") == "slideshow")
            $arrData['content'] = $this->checkFolder($arrData);

        return $arrData;
    }


    /**
     *
     * Show data in template file
     * @param string $modal
     * @param string $type
     * @param array $arrData
     * @return string
     */
    function showDataInTemplate($modal = "fancybox", $type = "ajax", $arrData)
    {
        $tmpFile = dirname(__FILE__) . DS . $modal . DS . 'tmpl' . DS . $type . ".php";
        if (file_exists($tmpFile)) {
            ob_start();
            require ($tmpFile);
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        } else {
            return "Template file not found.";
        }
    }


    /**
     *
     * Get template layout path
     * @param object $plugin
     * @param string $layout
     * @return string
     */
    function getLayoutPath($plugin, $layout = 'default')
    {
        global $mainframe;

        // Build the template and base path for the layout
        $tPath = JPATH_BASE . DS . 'templates' . DS . $mainframe->getTemplate() . DS . 'html' . DS . $plugin->name . DS . $layout . '.php';
        $bPath = JPATH_BASE . DS . 'plugins' . DS . $plugin->type . DS . $plugin->name . DS . 'tmpl' . DS . $layout . '.php';
        // If the template has a layout override use it
        if (file_exists($tPath)) {
            return $tPath;
        } elseif (file_exists($bPath)) {
            return $bPath;
        }
        return '';
    }


    /**
     *
     * Load layout content
     * @param object $plugin
     * @param string $layout
     * @return string
     */
    function loadLayout($plugin, $layout = 'default')
    {
        $layout_path = $this->getLayoutPath($plugin, $layout);
        if ($layout_path) {
            ob_start();
            require $layout_path;
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        }
        return '';
    }


    /**
     *
     * Return header library
     * @param string $bodyString
     * @param string $identifierString
     * @param string $headerString
     * @return string
     */
    function getHeaderLibraryHelper($bodyString, $identifierString, $headerString)
    {
        if (strpos($bodyString, $identifierString) === false) {
            $bodyString = str_replace('</head>', "\t" . implode("\n", $headerString) . "\n</head>", $bodyString);
        }
        return $bodyString;
    }


    /**
     *
     * Get User Agent
     * @return string user agent
     */
    function get_user_browser()
    {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $ub = '';
        if (preg_match('/MSIE/i', $u_agent)) {
            $ub = "ie";
        } elseif (preg_match('/Firefox/i', $u_agent)) {
            $ub = "firefox";
        } elseif (preg_match('/Safari/i', $u_agent)) {
            $ub = "safari";
        } elseif (preg_match('/Chrome/i', $u_agent)) {
            $ub = "chrome";
        } elseif (preg_match('/Flock/i', $u_agent)) {
            $ub = "flock";
        } elseif (preg_match('/Opera/i', $u_agent)) {
            $ub = "opera";
        }
        return $ub;
    }


    /**
     *
     * Check input data is Joomla folder and return image array
     * Else return base data
     * @param array $arrData
     * @return array
     */
    function checkFolder($arrData)
    {
        jimport('joomla.filesystem.folder');
        $modalContent = array();

        if (JFolder::exists(JPATH_ROOT . DS . $arrData['href'])) {
            $files = JFolder::files(JPATH_ROOT . DS . $arrData['href']);
            if (is_array($files)) {
                foreach ($files as $fileName) {
                    $info = pathinfo($fileName);
                    if (in_array($info['extension'], array("jpg", "jpeg", "bmp", "png"))) {
                        $modalContent[] = JURI::root() . "/" . str_replace(DS, "/", $arrData['href']) . "/" . $fileName;
                    }
                }
            } else
                break;
        } else {
            $modalContent = explode(", ", $arrData['href']);
        }
        return $modalContent;
    }
}

?>