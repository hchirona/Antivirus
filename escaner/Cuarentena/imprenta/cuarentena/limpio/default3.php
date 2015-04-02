<?php 
/*------------------------------------------------------------------------
# Copyright (C) 2005-2012 WebxSolution Ltd. All Rights Reserved.
# @license - GPLv2.0
# Author: WebxSolution Ltd
# Websites:  http://www.webxsolution.com
# Terms of Use: An extension that is derived from the JoomlaCK editor will only be allowed under the following conditions: http://joomlackeditor.com/terms-of-use
# ------------------------------------------------------------------------*/ 
defined('JPATH_BASE') or die;


?>
<script type="text/javascript">
function submitform(pressbutton){
	if (pressbutton) {
		document.adminForm.task.value=pressbutton;
	}
	if (typeof document.adminForm.onsubmit == "function") {
		document.adminForm.onsubmit();
	}
	document.adminForm.submit();
}

var Joomla = {};

Joomla.submitbutton = function(pressbutton) {
	submitform(pressbutton);
}

Joomla.changeDynaList = function( listname, source, key, orig_key, orig_val ) {
	var list = eval( 'document.adminForm.' + listname );

	// empty the list
	for (i in list.options.length) {
		list.options[i] = null;
	}
	i = 0;
	for (x in source) {
		if (source[x][0] == key) {
			opt = new Option();
			opt.value = source[x][1];
			opt.text = source[x][2];

			if ((orig_key == key && orig_val == opt.value) || i == 0) {
				opt.selected = true;
			}
			list.options[i++] = opt;
		}
	}
	list.length = i;
}

var templateStylesheets = [];
<?php
$i = 0;
foreach ($this->templateStylesheets as $k=>$items) {
	foreach ($items as $v) {
		echo "templateStylesheets[".$i++."] = [ '$k','".addslashes( $v->value )."','".addslashes( $v->text )."' ];\n\t\t";
	}
}
?>



</script>

<div class="header">
	<div class="innerHeader">
	<img src="images/headerTitle.png" height="48" width="155" />
	</div>
</div>
<form name="adminForm" id="adminForm" action="index.php" method="post">
<div class="container">	
	<div class="mainBody">
		<h3>Setup</h3>
		<fieldset class="adminform">
		<legend>Users Folders</legend>
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td><img align="middle" src="images/folders.png"/></td>
			<td colspan="2">Use specific user folders when using the file browser to upload and browse files<br /> (Note: If this is set to 'No' you can skip the settings below)</td>			
			<td width="20%" align="right"><?php echo $this->useUserFolderBooleanList;?></td>
		</tr>
		<tr>
			<td><img align="middle" src="images/folders.png"/></td>
			<td colspan="2">Use Folder Type(ID or Username)</td>			
			<td width="20%" align="right"><?php echo $this->userFolderTypeList;?></td>
		</tr>
		<?php if(isset( $this->userList)) : ?>
		<tr>
			<td><img align="middle" src="images/folders.png"/></td>
			<td colspan="2">Allow these users to see all folders</td>			
			<td width="20%"align="right"><?php echo $this->userList;?></td>
		</tr>
		<?php endif; ?>
		</table>
		</fieldset>
	</div>
</div>
<div class="buttons left">
<button onclick="document.adminForm.task.value='font';document.adminForm.submit();"><<< Prev</button>
</div>
<div class="buttons">
<input type="submit" value="Next  >>>" />
</div>
<input type="hidden" name="task" value="template" />
 </form>	