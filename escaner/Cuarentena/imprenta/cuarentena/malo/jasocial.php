<?php
/**
 * ------------------------------------------------------------------------
 * JA System Social Plugin for Joomla 2.5 & 3.0
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
if(!defined('DS')){
	define('DS', DIRECTORY_SEPARATOR);
}
jimport('joomla.plugin.plugin');
jimport('joomla.html.parameter');

class plgSystemJASocial extends JPlugin
{

    var $plugin;
    var $plgParams;
    var $_plgCode = "#{jasocial(.*?)}#i";
    var $_plgCodeHolder = "{JASOCIAL-%d}";
    var $_plgCodeModifier = "#{jasocial(.*?)}#e";
    var $_plgCodeMakeHolder = "'{JASOCIAL-' . plgSystemJASocial::getCount() . '}'";
    var $position;
    var $source;
	var $_article = '';

    /**
     * Object Constructor.
     *
     * @access	public
     * @param	object	The object to observe -- event dispatcher.
     * @param	object	The configuration object for the plugin.
     * @return	void
     * @since	1.7
     */
    function plgSystemJASocial(&$subject, $config)
    {
		$mainframe = JFactory::getApplication();
		if($mainframe->isAdmin()){
			return;
		}
        parent::__construct($subject, $config);
        $this->plugin = JPluginHelper::getPlugin('system', 'jasocial');
        $this->plgParams = new JRegistry($this->plugin->params);
        $this->position = $this->plgParams->get('position', 'beforecontent');
        $this->source = $this->plgParams->get('source', 'both');
        $this->loadLanguage('plg_' . $this->plugin->type . '_' . $this->plugin->name, JPATH_ADMINISTRATOR);
    }

    function onAfterRoute(){
        //do not call getTemplate when Joomla not finish routing
        $this->stylesheet($this->plugin);
    }


    /**
     *
     * Add style sheet
     */
    function onAfterDispath()
    {	
        // Dohq: Fix bug - can't change template via menu
        // Vutd : not add with joomla 2.5
	
		// $this->stylesheet($this->plugin);
    }


    /**
     *
     * Number of social code tag
     * @param boolean $reset
     * @return int
     */
    function getCount($reset = false)
    {
        static $count = 0;
        if ($reset)
            $count = -1;
        return $count++;
    }


    /**
     *
     * Show social button before display content
     * @param string $context key of content type
     * @param object $article
     * @param object $params
     * @param int $page page number
     * @return string social button
     */
    public function onContentBeforeDisplay($context, &$article, &$params, $page = 0)
    {
        if ($this->position == 'beforecontent') {
            return $this->onArticles($context, $article, $params, $page );
        }
        return "";

    }


    /**
     *
     * Show social button after display content
     * @param string $context key of content type
     * @param object $article
     * @param object $params
     * @param int $page page number
     * @return string social button
     */
    public function onContentAfterDisplay($context, &$article, &$params, $page = 0)
    {
        if ($this->position == 'aftercontent') {
            return $this->onArticles($context, $article, $params, $page );
        }
        return "";

    }


    /**
     *
     * Show social button after display title
     * @param string $context key of content type
     * @param object $article
     * @param object $params
     * @param int $page page number
     * @return string social button
     */
    public function onContentAfterTitle($context, &$article, &$params, $page = 0)
    {
        if ($this->position == 'aftertitle') {
            return $this->onArticles($context, $article, $params, $page);
        }
        return "";
    }


    /**
     *
     * Process config to display button type
     * @param string $context article key
     * @param object $article
     * @param object $params
     * @param int $page page number
     * @return string social buttons
     */
    function onArticles($context, &$article, &$params, $page = 0)
    {
        $mainframe = JFactory::getApplication();
        if ($mainframe->isAdmin()) {
            return '';
        }
        $option = JRequest::getVar('option');
        $view = JRequest::getVar('view');

        $plgParams = $this->plgParams;
		$catid = null;
		if(isset($article->catid)){
			$catid = $article->catid;
		}
        $catids = $plgParams->get('catsid', '');

        $isEmpty = $this->checkEmptyArray($catids);
        if (is_array($catids) && !$isEmpty) {
            $categories = $catids;
        } elseif ($catids == '' || $isEmpty) {
            $categories[] = $catid;
        } else {
            $categories[] = $catids;
        }

        if (!in_array($catid, $categories)) {
            return "";
        }
		
        if ($context == 'com_content.article' || $context == 'com_content.featured' || $context=='com_content.category') {
			$this->_article = $article;
            $display_on_home = (int) $plgParams->get('display_on_home', 1);
            $display_on_list = (int) $plgParams->get('display_on_list', 1);
            $id = JRequest::getInt('id');

            if ($view == 'featured' && $display_on_home != 1)
                return "";
            if (($view == 'categories') && ($display_on_list != 1))
                return "";
            if (($view == 'category') && ($display_on_list != 1))
                return "";
            $catid = $article->catid;
            $catids = $plgParams->get('catsid', '');

            $isEmpty = $this->checkEmptyArray($catids);
            if (is_array($catids) && !$isEmpty) {
                $categories = $catids;
            } elseif ($catids == '' || $isEmpty) {
                $categories[] = $catid;
            } else {
                $categories[] = $catids;
            }

            if (!in_array($catid, $categories)) {
                return '';
            }
            if (!isset($article->readmore_link)) {
                $article->readmore_link = JRoute::_(ContentHelperRoute::getArticleRoute(isset($article->slug) ? $article->slug : "", isset($article->catslug) ? $article->catslug : ""));
            }
            $path = $article->readmore_link;
            if (!preg_match("/^\//", $path)) {
                //convert to relative url
                $path = JURI::root(true) . '/' . $path;
            }
            //convert to absolute url
            $path = $this->getRootUrl() . $path;
            $button = $this->renderButton($path, $article->title);
            return $button;
        }

        return '';
    }


    /**
     *
     * Render social button
     * @param string $path
     * @param sting $title
     * @return string social button
     */
    function renderButton($path, $title)
    {
        $button = '';
        $params = $this->plgParams;

        if ($this->plgParams->get('fb_like_group', 'fb_like_disable') == 'fb_like_enable') {
            $button .= $this->facebook_like_button($path, $params);
        }
        if ($this->plgParams->get('tw_share_group', 'tw_share_disable') == 'tw_share_enable') {
            $button .= $this->twitter_share_button($path, $title, $params);
        }
        if ($this->plgParams->get('google_plus_group', 'google_plus_enable') == 'google_plus_enable') {
            $button .= $this->googlePlusOneButton($path, $title);
        }
         /* Remove by vu.tran
        if ($this->plgParams->get('buzz_post_group', 'buzz_post_disable') == 'buzz_post_enable') {
            $button .= $this->buzz();
        }
       	*/
        if ($this->plgParams->get('digg_post_group', 'digg_post_disable') == 'digg_post_enable') {
            $button .= $this->digg($path);
        }
		
        if ($this->plgParams->get('fb_like_embed', 'iframe') == 'iframe') {
            if ($this->plgParams->get('fb_share_group', 'fb_share_disable') == 'fb_share_enable') {
                $button .= $this->facebook_share_button($path, $params);
            }
        }

        if ($button != '') {
            $button = '<div class="ja_social' . $this->position . '">' . $button . '</div>';
        }
        return $button;
    }


    /**
     *
     * Render facebook like button
     * @param string $link
     * @param object $plgParams
     * @return string facebook like button
     */
    function facebook_like_button($link, $plgParams)
    {
        $send = (int) $plgParams->get('display_send_button', 1);
        $send = $send > 0 ? 'true' : 'false' ;
        $fb_embed = $plgParams->get('fb_like_embed', 'iframe');
        $fb_layout = $plgParams->get('fb_like_layout', 'button_count');
        $fb_show_faces = $plgParams->get('fb_like_show_faces', 1);
        $fb_width = $plgParams->get('fb_like_width', 450);
        $fb_height = $plgParams->get('fb_like_height', 70);
        $fb_action = $plgParams->get('fb_like_action', 'like');
        $fb_font = $plgParams->get('fb_like_font', 'arial');
        $fb_color = $plgParams->get('fb_like_color', 'light');

        if ($fb_embed == 'fbml') {
            $iframe = '<fb:like href="' . $link . '" layout="' . $fb_layout . '" show_faces="' . $fb_show_faces . '" action="' . $fb_action . '" colorscheme="' . $fb_color . '" font="' . $fb_font . '" send="' . $send . '"></fb:like>  ';
        } else {
            $link = "http://www.facebook.com/plugins/like.php?href=" . rawurlencode($link) . "&amp;layout=" . $fb_layout . "&amp;show_faces=" . $fb_show_faces . "&amp;width=" . $fb_width . "&amp;action=" . $fb_action . "&amp;colorscheme=" . $fb_color . "&amp;font=" . $fb_font . "&amp;send=" . $send;
            $iframe = "<iframe class=\"facebook-like\" name=\"ja-facebook-like\" src=\"{$link}\" ";
            $iframe .= "scrolling=\"no\" frameborder=\"0\" width=\"{$fb_width}\" style=\"border:none; overflow:hidden; height:" . $fb_height . "px;\"></iframe>";
        }
        $iframe = "<div class=\"ja-fblike-button " . $fb_layout . " \" style=\"width:auto; \">{$iframe}</div>";

        return $iframe;
    }


    /**
     *
     * Render facebook share button
     * @param string $link
     * @param object $plgParams
     * @return string facebook share button
     */
    function facebook_share_button($link, $plgParams)
    {
        $type = $plgParams->get('fb_share_type', 'button');

        $html = '<div class="ja-fbshare-button  ' . $type . '">
				<script type="text/javascript">
				/* <![CDATA[ */
				document.write(\'<fb:share-button class="url" href="' . $link . '" type="' . $type . '"></fb:share-button>\');
				/* ]]> */
				</script>
				</div>
				';
        return $html;
    }


    /**
     *
     * Render digg button
     * @param string $url
     * @return string digg button
     */
    function digg($url)
    {
        $document = JFactory::getDocument();
        $buttonstyle = $this->plgParams->get('digg_button_style', 'DiggMedium');
        $html = '
		<script type="text/javascript" src="http://widgets.digg.com/buttons.js"></script>
		<div class="digg-button-share ' . $buttonstyle . '">
			<a class="DiggThisButton ' . $buttonstyle . '" href="http://digg.com/submit?url=' . $url . '">
			</a>
		</div>';
        return $html;
    }


    protected function checkEmptyArray($arr = array())
    {
        if (!empty($arr)) {
            $count = count($arr);
            $check = 0;
            foreach ($arr as $id => $item) {
                if (empty($item))
                    $check++;
            }
            if ($check != $count)
                return false;
        }
        return true;
    }


    /**
     *
     * Render twitter share button
     * @param string $url
     * @param string $title
     * @param object $plgParams
     * @return string twitter share button
     */
    function twitter_share_button($url, $title = '', $plgParams)
    {
        $style = $plgParams->get('tw_data_count', 'vertical');
        $lang = $plgParams->get('tw_lang', 'en');
        $data_via = $plgParams->get('tw_data_via', 'JoomlartDemo');

        $data_related = $plgParams->get('tw_data_related', 'joomlart');
        $desc = $plgParams->get('tw_data_related_desc', '');
        if (!empty($data_related)) {
            $data_related = $data_related . ':' . $desc . '"';
        }

        $shareUrl = "http://twitter.com/share";
        $shareUrl .= "?text=" . rawurlencode($title);
        $shareUrl .= "&amp;count=" . rawurlencode($style);
        $shareUrl .= "&amp;lang=" . rawurlencode($lang);
        if (!empty($data_via))
            $shareUrl .= "&amp;via=" . rawurlencode($data_via);

        if (!empty($data_related)) {
            if (!empty($desc)) {
                $shareUrl .= "&amp;related=" . rawurlencode($data_related . ':' . $desc);
            } else {
                $shareUrl .= "&amp;related=" . rawurlencode($data_related);
            }
        }
        $shareUrl .= "&amp;url=" . rawurlencode($url);
        //$shareUrl .= "&amp;counturl=" . urlencode($sefurl);
        $shareUrl .= "&amp;counturl=" . rawurlencode($url);

        $html = '';
        if (!defined('JA_USE_TWITTER_WIDGET')) {
            define('JA_USE_TWITTER_WIDGET', 1);
            $html .= '<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>';
        }
        $html .= '
				<div class="ja-retweet-button ' . $style . '">
				   <a href="' . $shareUrl . '" class="twitter-share-button">' . JText::_('Tweet') . '</a>
				</div>';
        return $html;
    }


    /**
     *
     * Render buzz button
     * @return string google buzz button
     */
    function buzz()
    {
        $buzzTitle = $this->plgParams->get('BuzzTitle', 'Google Buzz');
        $buzzStyle = $this->plgParams->get('buzz_post_btype', 'normal-count');
        $buzz = '<div class="gbuzz-share-button">
				<a title="' . $buzzTitle . '"
					class="google-buzz-button"
					href="http://www.google.com/buzz/post"
					data-button-style="' . $buzzStyle . '"
					data-locale="vi"></a>
				</div>
				<script type="text/javascript" src="http://www.google.com/buzz/api/button.js"></script>';

        return $buzz;
    }


    /**
     *
     * Render google plus button
     * @param string $url
     * @param string $title
     * @return string google plus button
     */
    function googlePlusOneButton($url, $title = '')
    {
        static $button = '';
        if ($button == '') {
            $style = $this->plgParams->get('google_plus_button_style', 'standard');
            $count = (int) $this->plgParams->get('google_plus_include_count', 1);

            $sstyle = ($style != 'standard') ? ' size="' . $style . '"' : '';
            $count = !$count ? ' count="false"' : '';

            $button = '<div class="gplusone-share-button ' . $style . '"><g:plusone' . $sstyle . $count . '></g:plusone></div>';
        }
        return $button;
    }


    /**
     *
     * Add google plus javascript
     * @return void
     */
    function googlePlusOneLib()
    {
        $lang = $this->plgParams->get('google_plus_language', 'en-US');

        $script = "<!-- Place this tag after the last plusone tag -->
				<script type=\"text/javascript\" language=\"javascript\">
				  window.___gcfg = {lang: '" . $lang . "'};

				  (function() {
				    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
				    po.src = 'https://apis.google.com/js/plusone.js';
				    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
				  })();
				</script>";
        return $script;
    }


     function onAfterRender()
    {
        $mainframe = JFactory::getApplication();
        if ($mainframe->isAdmin()) {
            return '';
        }

        //update facebook meta tags
      	//var_dump($this->_article->id);  
      
        $document 		= JFactory::getDocument();

        $body 			= JResponse::getBody();

        //include FBML
        $plgParams 		= $this->plgParams;
        $fb_embed 		= $plgParams->get('fb_like_embed', 'iframe');
        $fb_share 		= $plgParams->get('fb_share_group', 'fb_share_disable');
		$app_id   		= $plgParams->get('app_id', '');
		$imageshare			= '';
		$descriptionshare 	= '';
		$titleshare		= $document->getTitle();
		if(isset($this->_article) && isset($this->_article->fulltext)){
			
			//var_dump($this->_article);
			
			$titleshare = htmlspecialchars($this->_article->title);
			//Search images of content
			if (isset($this->_article->images)) {
				$images = json_decode($this->_article->images);
			}
			if((isset($images->image_fulltext) and !empty($images->image_fulltext)) || (isset($images->image_intro) and !empty($images->image_intro))){
			     $imageshare = (isset($images->image_fulltext) and !empty($images->image_fulltext))?$images->image_fulltext:((isset($images->image_intro) and !empty($images->image_intro))?$images->image_intro:"");
			
			}else{
				$regex = "/\<img.+?src\s*=\s*[\"|\']([^\"]*)[\"|\'][^\>]*\>/";
				preg_match($regex, $this->_article->introtext.$this->_article->fulltext, $matches);
				
				$images = (count($matches)) ? $matches : array();
				if (count($images)) {
					$imageshare = $images[1];
				}
			}
			
			$path = JURI::root();
			/**
			* Remove http://
			* Remove http://www
			*/
			$path = str_replace('http://www.','',$path);
			$path = str_replace('http://','',$path);
			
			/**
			* Find path
			*/
			$pos  = strpos($imageshare,$path);
			/**
			* If false - add url root
			*/
			if($pos == false){
				$imageshare = JURI::root().$imageshare;
			}
			//Get content
			$conenttext 	= strip_tags($this->_article->introtext.$this->_article->fulltext);
			$descriptionshare = substr($conenttext,0,400);
			$descriptionshare = strip_tags($descriptionshare);
		}
        if ($fb_embed == 'fbml' || $fb_share == 'fb_share_enable') {
            $fbml = $this->getFBML();
            if (!empty($fbml)) {
                $body = $this->str_ireplace('</body>', $fbml . '</body>' . "\r\n", $body, 1);
                //Declares a namespace for FBML tags in an HTML document.
                //add xmlns:fb="http://www.facebook.com/2008/fbml" to header tag
                $regex = "/<html.*?xmlns:fb=\".*?\"[^>]*?>/i";
                if (!preg_match($regex, $body)) {
                    $body = str_replace('<html', '<html xmlns:fb="http://www.facebook.com/2008/fbml" ', $body);
                }
                //open graph namespace
                $regex = "/<html.*?xmlns:og=\".*?\"[^>]*?>/i";
                if (!preg_match($regex, $body)) {
                    $body = str_replace('<html', '<html xmlns:og="http://ogp.me/ns#" ', $body);
                }
            }
        }
		if($this->plgParams->get('fb_like_group', 'fb_like_disable') == 'fb_like_enable'){
			$tags = '';
           	$tags .= '<meta property="og:title" content="' . $titleshare . '"/>' . "\r\n";
            $tags .= '<meta property="og:site_name" content="' . $document->getDescription() . '"/>' . "\r\n";
            if($imageshare){
            	$tags .= '<meta property="og:image" content="' . $imageshare . '"/>';
            }
            if(!empty($descriptionshare)){
            	$tags .= '<meta name="og:description" content="'.$descriptionshare.'" />';
            }
            $tags .= '<meta name="og:url" content="'.$this->curPageURL().'" />';
        	if(!empty($app_id)) {
				$tags .= '<meta property="og:app_id" content="' . $app_id . '" />';
			}
            $body = $this->str_ireplace('<head>', '<head>' . "\r\n" . $tags, $body, 1);
		}
		
        if ($this->plgParams->get('google_plus_group', 'google_plus_enable') == 'google_plus_enable') {
			$tagsg  = '';
			$tagsg .= '<meta itemprop="name" content="' . $titleshare . '" />';
			if(!empty($descriptionshare)){
            	$tagsg .= '<meta itemprop="description" content="'.$descriptionshare.'" />';
            }
			if($imageshare){
            	$tagsg .= '<meta itemprop="image" content="' . $imageshare . '" />';
            }
			$body = $this->str_ireplace('<head>', '<head>' . "\r\n" . $tagsg, $body, 1);
			
			
            $body = str_ireplace('</body>', $this->googlePlusOneLib() . "\r\n" . '</body>', $body);
        }

        JResponse::setBody($body);
        $body = JResponse::getBody();
        $current = JURI::getInstance()->toString(); //JURI::current();
        if (!preg_match($this->_plgCode, $body)) {
            return;
        }
        $this->_aUSetting = $this->getUserSetting($body);
        if (count($this->_aUSetting) > 0) {
            foreach ($this->_aUSetting as $id => $sSetting) {
                $button = $this->renderButton($current, JText::_('Share_This'));
                $holder = sprintf($this->_plgCodeHolder, $id);
                $body = str_replace($holder, $button, $body);
            }
        }
        JResponse::setBody($body);
    }
	
	/**
	* Get current url
	* 
	*/
	function curPageURL() {
		$pageURL = 'http';
		 
		if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"]== "on") {$pageURL .= "s";}
		$pageURL .= "://";
		if (isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] != "80") {
		  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
		  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}
    /**
     *
     * Facebook connect method
     */
    function getFBML()
    {
        if (!defined('JA_INCLUDE_FBML')) {
            define('JA_INCLUDE_FBML', 1);
            $plgParams = $this->plgParams;
            $app_id = $plgParams->get('fb_like_app_id', '');

            $fbml = "
			<div id=\"fb-root\"></div>
			<script type=\"text/javascript\">
			  window.fbAsyncInit = function() {
			    FB.init({appId: '{$app_id}', status: true, cookie: true, xfbml: true});
			  };
			  (function() {
			    var e = document.createElement('script');
			    e.type = 'text/javascript';
			    e.async = true;
			    e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
			    document.getElementById('fb-root').appendChild(e);
			  }());
			  (function(d, s, id) {
                  var js, fjs = d.getElementsByTagName(s)[0];
                  if (d.getElementById(id)) {return;}
                  js = d.createElement(s); js.id = id;
                  js.src = '//connect.facebook.net/en_US/all.js#xfbml=1';
                  fjs.parentNode.insertBefore(js, fjs);
               }(document, 'script', 'facebook-jssdk'));
			</script>
			";
            return $fbml;
        }
        return '';
    }


    /**
     *
     * Setting from User
     * @param string $text
     * @return array
     *
     */
    function getUserSetting(&$text)
    {
        if (preg_match_all($this->_plgCode, $text, $matches)) {
            $text = preg_replace($this->_plgCodeModifier, $this->_plgCodeMakeHolder, $text, -1, $count);
            return $matches[1];
        } else {
            return array();
        }

    }


    /**
     *
     * Replace a patten in string
     * @param string $search patten for search
     * @param string $replace
     * @param string $str string input
     * @param int $count number will replace
     * @return string
     */
    function str_ireplace($search, $replace, $str, $count = NULL)
    {

        if (!is_array($search)) {

            $slen = strlen($search);
            if ($slen == 0) {
                return $str;
            }

            $lendif = strlen($replace) - strlen($search);
            $search = utf8_strtolower($search);

            $search = preg_quote($search, '/');
            $lstr = utf8_strtolower($str);
            $i = 0;
            $matched = 0;
            while (preg_match('/(.*)' . $search . '/Us', $lstr, $matches)) {
                if ($i === $count) {
                    break;
                }
                $mlen = strlen($matches[0]);
                $lstr = substr($lstr, $mlen);
                $str = substr_replace($str, $replace, $matched + strlen($matches[1]), $slen);
                $matched += $mlen + $lendif;
                $i++;
            }
            return $str;

        } else {

            foreach (array_keys($search) as $k) {

                if (is_array($replace)) {

                    if (array_key_exists($k, $replace)) {

                        $str = utf8_ireplace($search[$k], $replace[$k], $str, $count);

                    } else {

                        $str = utf8_ireplace($search[$k], '', $str, $count);

                    }

                } else {

                    $str = utf8_ireplace($search[$k], $replace, $str, $count);

                }
            }
            return $str;

        }

    }


    /**
     * Get url site root
     * @return (string) - root url without last slashes
     */
    function getRootUrl()
    {
        $url = str_replace(JURI::root(true), '', JURI::root());
        $url = preg_replace("/\/+$/", '', $url);
        return $url;
    }


    /**
     *
     * Add style into header
     * @param object $plugin
     * @return void
     */
    function stylesheet($plugin)
    {
        $mainframe = JFactory::getApplication();
        JHTML::stylesheet('plugins/' . $plugin->type . '/' . $plugin->name . '/assets/style.css');
        if (is_file(JPATH_SITE . DS . 'templates' . DS . $mainframe->getTemplate() . DS . 'css' . DS . $plugin->name . ".css")) {
            //overwrite with template stylesheet
            JHTML::stylesheet($plugin->name . ".css", 'templates/' . $mainframe->getTemplate() . '/css/');
        }
    }
}