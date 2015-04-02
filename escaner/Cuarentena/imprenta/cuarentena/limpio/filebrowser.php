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


class plgAuthenticateFilebrowser extends JPlugin 
{
		
  	function plgAuthenticateFilebrowser(& $subject, $config) 
	{
		parent::__construct($subject, $config);
	}

	function authorise()
	{		
				
		//set component option in session
		$session = JFactory::getSession();
		$user = JFactory::getUser();
		
       
		$option = $session->get('jckoption');
        
		if(isset($option) && ($user->authorise('core.create', $option) || $user->authorise('core.edit', $option))) 
		    return true;
       
        if($user->authorise('core.create', 'com_content') || $user->authorise('core.edit', 'com_content')) 
	   	     return true;
	   
		//okay last chance! Lets check if asscoiated user groups have been added as an execption to override above checks
        $plugin = JPluginHelper::getPlugin('editors','jckeditor');
        $params = @ new JRegistry($plugin->params);
		$groups	= $user->getAuthorisedGroups();
		$filebrowserGroups 	= $params->get( 'filebrowsergroups', false );
		if( $groups && $filebrowserGroups)
		{
			for( $n=0, $i=count($groups); $n<$i; $n++ )
			{
				if( in_array( $groups[$n], $filebrowserGroups ) )
				{
					//Seems this user is able to access file browser.
					return true;
				}//end if
			}//end for loop
		}//end if
    
		return false;
	}

}