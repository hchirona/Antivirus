<?php
/*------------------------------------------------------------------------
# Copyright (C) 2005-2012 WebxSolution Ltd. All Rights Reserved.
# @license - GPLv2.0
# Author: WebxSolution Ltd
# Websites:  http://www.webxsolution.com
# Terms of Use: An extension that is derived from the JoomlaCK editor will only be allowed under the following conditions: http://joomlackeditor.com/terms-of-use
# ------------------------------------------------------------------------*/ 

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.event.plugin');
jckimport('ckeditor.htmlwriter.javascript');


class plgEditor348Update extends JPlugin 
{
		
  	
	private $_overwrite = false;
	
	
	
	function plgEditor348Update(& $subject, $config) 
	{
		parent::__construct($subject, $config);
	}

	function beforeLoad(&$params)
	{
		//lets create JS object
		$javascript = new JCKJavascript();
		
		if($this->_overwrite)
		{
			$javascript->addScriptDeclaration(
				"editor.on( 'configLoaded', function()
				{
					editor.config.plugins = 'html5support,' + editor.config.plugins
					if(editor.config.extraPlugins)
						editor.config.extraPlugins += ',video,audio,uicolor,imagedragndrop,ie9selectionoverride';
					else 	
						editor.config.extraPlugins += 'video,audio,uicolor,imagedragndrop,ie9selectionoverride';
				
					if(editor.config.toolbar == 'Full')
					{
						var toolbar = editor.config.toolbar_Full[editor.config.toolbar_Full.length-1];
						var extra = ['Video','Audio','UIColor'];
						editor.config.toolbar_Full[editor.config.toolbar_Full.length-1] = toolbar.concat(extra);
					}	
				});"	
			);
		}
         
		$config = JFactory::getConfig();
		$dbname = $config->get('db');
		
		$db = JFactory::getDBO();
				
		$query = "SELECT COUNT(1)
		FROM information_schema.tables 
		WHERE table_schema = '".$dbname."' 
		AND table_name = '".$db->getPrefix()."jcktoolbarplugins'";			
		
		$db->setQuery($query); 
		
		if(!$db->loadResult())
			return $javascript->toRaw();
		
		$query = "SELECT COUNT(p.id) AS pcount,COUNT(tp.pluginid) AS tpcount FROM #__jckplugins p
		LEFT JOIN #__jcktoolbarplugins tp on tp.pluginid = p.id
		WHERE `name` IN('html5support','video','audio','uicolor') ";

		$db->setQuery($query); 
		
		$info = $db->loadObject();

	    if($info && $info->tpcount)
			return;
		
            
		if(!$info->pcount)
		{ 
			$query = "INSERT INTO #__jckplugins (`title`,`name`,`type`,`row`,`published`,`editable`,`icon`,`iscore`,`params`, `parentid`) VALUES 
			('','html5support','plugin',0,1,1,'',1,'',NULL)";
			$db->setQuery($query);
		
			if(!$db->query())
				return $javascript->toRaw();
			
			$parentid = $db->insertid();
				                
            $query = "INSERT INTO #__jckplugins (`title`,`name`,`type`,`row`,`published`,`editable`,`icon`,`iscore`,`params`, `parentid`) VALUES 
            ('Video','video','plugin',3,1,1,'images/icon.png',1,'',".$parentid."),	
            ('Audio','audio','plugin',3,1,1,'images/icon.png',1,'',".$parentid."),	
            ('UIColor','uicolor','plugin',3,1,1,'uicolor.gif',1,'',NULL),	
            ('','imagedragndrop','plugin',0,1,1,'',1,'',NULL),
			('','ie9selectionoverride','plugin',0,1,1,'',1,'',NULL)";			
            $db->setQuery($query);
                    
            if(!$db->query())
                 return $javascript->toRaw();
         
            $first = $db->insertid();	
            
            $last = $first+2;
            
            //get next layout row  details
            
            $query =	"SELECT row as rowid,MAX(`ordering`) +1 AS rowordering FROM #__jcktoolbarplugins WHERE toolbarid = 1 
                        GROUP BY row
                        ORDER BY row DESC LIMIT 1";
            $db->setQuery($query); 
            $rowDetail = $db->loadObject();
                        
            $values = array();
            
            for($i = $first; $i <= $last; $i++)
                $values[] = '(1,'. $i.','.$rowDetail->rowid.','.$rowDetail->rowordering++.',1)';
                
            $query =  "INSERT INTO #__jcktoolbarplugins(toolbarid,pluginid,row,ordering,state) VALUES "	. implode(",",$values);
        
            $db->setQuery($query);
            $db->query();
		}
   
		if($this->_overwrite)
		{
			//Get toolbar plugins object
			
			jckimport('ckeditor.plugins');
			jckimport('ckeditor.plugins.toolbarplugins');
			$plugins = new JCKtoolbarPlugins();
			
			foreach(get_object_vars($plugins)as $key=>$value)
			{						
				if(strpos('p'.$key,'_'))
				unset($plugins->$key);	
			}
			
			$plugins->html5support = 1;
			$plugins->video = 1;
			$plugins->audio = 1;
			$plugins->uicolor = 1;
			$plugins->imagedragndrop = 1;
			$plugins->ie9selectionoverride =1;
			
			$config = new JRegistry('config');
			$config->loadObject($plugins);
					  
			 $cfgFile = ''; 
			 
			 $is1_6plus = file_exists(JPATH_PLUGINS.DS.'editors'.DS.'jckeditor'.DS.'jckeditor'.DS.'includes'.DS.'ckeditor');
			 
			 if($is1_6plus)
				$cfgFile = JPATH_PLUGINS.DS.'editors'.DS.'jckeditor'.DS.'jckeditor'.DS.'includes'.DS.'ckeditor'.DS.'plugins'.DS.'toolbarplugins.php';	
			else
				$cfgFile = JPATH_PLUGINS.DS.'editors'.DS.'jckeditor'.DS.'includes'.DS.'ckeditor'.DS.'plugins'.DS.'toolbarplugins.php';	
			
			// Get the config registry in PHP class format and write it to file
			if($is1_6plus)
			{
				if(!JFile::write($cfgFile, $config->toString('PHP', array('class' => 'JCKToolbarPlugins extends JCKPlugins'))))
					return $javascript->toRaw(); //if fail then bail out
			}
			else
			{
				 if (!JFile::write($cfgFile, $config->toString('PHP', 'config', array('class' => 'JCKToolbarPlugins extends JCKPlugins')))) 
				 	return $javascript->toRaw(); //if fail then bail out
			}
				 
			jckimport('ckeditor.toolbar');
			jckimport('ckeditor.toolbar.full');
			
			$toolbar = new JCKFull();
					
			//fix toolbar values or they will get wiped out
			foreach (get_object_vars( $toolbar ) as $k => $v)
			{
				if(is_null($v))
				{
					$toolbar->$k = ''; 
				}
				if($k[0] == '_')
					$toolbar->$k = NULL;
			}
			
			if(isset($toolbar->Video) || isset($toolbar->Audio) || isset($toolbar->UIColor))
				return false;
			
			$toolbar->Video = '';
			$toolbar->Audio = '';
			$toolbar->UIColor = '';
					
			$toolbarConfig = new JRegistry('toolbar');
			$toolbarConfig->loadObject($toolbar);	
			
			$filename = '';
			
			if($is1_6plus)
				$filename = JPATH_PLUGINS.DS.'editors'.DS.'jckeditor'.DS.'jckeditor'.DS.'includes'.DS.'ckeditor'.DS.'toolbar'.DS.'full.php';	
			else
				$filename = JPATH_PLUGINS.DS.'editors'.DS.'jckeditor'.DS.'includes'.DS.'ckeditor'.DS.'toolbar'.DS.'full.php';	
			
			// Get the config registry in PHP class format and write it to file
			if($is1_6plus)
			{
				JFile::write($filename, $toolbarConfig->toString('PHP', array('class' => 'JCKFull extends JCKToolbar')));
			}
			else
			{
				JFile::write($filename, $toolbarConfig->toString('PHP','toolbar', array('class' => 'JCKFull extends JCKToolbar')));
			}	
		}				
		return $javascript->toRaw();
		
	}

}