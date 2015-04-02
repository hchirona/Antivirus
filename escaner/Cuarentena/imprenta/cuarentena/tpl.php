<?php
/**
 * ------------------------------------------------------------------------
 * JA Facebook Activity Module for J25 & J30
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */
// no direct access
defined ( '_JEXEC' ) or die ( 'Restricted access' );
?>
<script type="text/javascript">
	var profiles = <?php echo json_encode($jsonData)?>;
	var Tempprofiles = <?php echo json_encode($jsonTempData)?>;
	var japarams2 = null;
	var lg_confirm_to_cancel = '<?php echo JText::_('ARE_YOUR_SURE_TO_CANCEL')?>';
	var lg_enter_profile_name = '<?php echo JText::_('ENTER_PROFILE_NAME')?>';
	var lg_please_enter_profile_name = '<?php echo JText::_('PROFILE_NAME_NOT_EMPTY')?>';
	var lg_confirm_delete_profile = '<?php echo JText::_('CONFIRM_DELETE_PROFILE')?>';
	var mod_url = '../modules/mod_jaslideshow/admin/helper.php';
	var templateactive = '<?php echo $template?>';
	window.addEvent('load', function(){
		japarams2 = new JAPARAM2('jformparams<?php echo str_replace("holder","",$this->fieldname);?>');
		japarams2.changeProfile($('jformparams<?php echo str_replace("holder","",$this->fieldname);?>').value);
	});
</script>

<div class="ja-profile">
	<label class="hasTip" for="jform_params_<?php echo $this->field_name?>" id="jform_params_<?php echo $this->field_name?>-lbl" title="<?php echo JText::_($this->element['description'])?>"><?php echo JText::_($this->element["label"])?></label>
	<?php echo $HTML_Profile?>
	<div class="profile_action">
		<span class="clone">
			<a href="javascript:void(0)" onclick="japarams2.cloneProfile()" title="<?php echo JText::_('CLONE_DESC')?>"><?php echo JText::_('Clone')?></a>
		</span>
		| 
		<span class="delete">
			<a href="javascript:void(0)" onclick="japarams2.deleteProfile()" title="<?php echo JText::_('DELETE_DESC')?>"><?php echo JText::_('Delete')?></a>
		</span>	
	</div>
</div>
</li>

<?php		
$fieldSets = $paramsForm->getFieldsets('params');

foreach ($fieldSets as $name => $fieldSet) :
	if (isset($fieldSet->description) && trim($fieldSet->description)) :
		echo '<p class="tip">'.JText::_($fieldSet->description).'</p>';
	endif;?>
		
	<?php $hidden_fields = ''; ?>
	<?php foreach ($paramsForm->getFieldset($name) as $field) : ?>
	<?php if (!$field->hidden) : ?>
	<li>
		<?php echo $paramsForm->getLabel($field->fieldname,$field->group); ?>
		<?php echo $paramsForm->getInput($field->fieldname,$field->group); ?>
	</li>
	<?php else : $hidden_fields.= $paramsForm->getInput($field->fieldname,$field->group); ?>
	<?php endif; ?>
	<?php endforeach; ?>
	<?php echo $hidden_fields; ?>
<?php endforeach; ?>
	
	
<li>
<script type="text/javascript"><!--
	window.addEvent('load', function(){
		Joomla.submitbutton = function(task)
		{
				if (task == 'module.cancel' || document.formvalidator.isValid(document.id('module-form'))) {	
					if(task != 'module.cancel' && document.formvalidator.isValid(document.id('module-form'))){
						japarams2.saveProfile(task);
					}else if(task == 'module.cancel' || document.formvalidator.isValid(document.id('module-form'))){
						Joomla.submitform(task, document.getElementById('module-form'));
					}
					if (self != top) {
						window.top.setTimeout('window.parent.SqueezeBox.close()', 1000);
					}
				} else {
					alert('Invalid form');
				}
		}

	});
</script>