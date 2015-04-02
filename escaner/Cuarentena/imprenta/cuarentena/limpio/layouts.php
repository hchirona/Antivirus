<?php
/**
 * ------------------------------------------------------------------------
 * JA T3v2 System Plugin for J25 & J30
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

// No direct access
defined('_JEXEC') or die;
?>
<table class="ja-layout-titles admintable">
    <tr>
        <th width="15" style="border-left: none !important">
            <?php echo JText::_('#')?>
        </th>
        <th>
            <?php echo JText::_('LAYOUT_NAME')?>
        </th>
        <th>
            <?php echo JText::_('Action')?>
        </th>
    </tr>
    <?php $i=0?>
    <?php if($layouts){?>
        <?php if(isset($layouts['default'])){?>
        <tr id="layout_default" class="row<?php echo $i?>">
            <td width="15" style="border-left: none !important">
                <?php echo 1?>
            </td>
            <td>
                Default
            </td>
            <td>
            <?php if ($isNewFolderStruct): ?>
                <span class="edit" data-layout="default" title="<?php echo JText::_('CLICK_HERE_TO_EDIT_THIS_LAYOUT')?>"><?php echo JText::_('Edit')?></span>
                <span class="clone" data-layout="default" title="<?php echo JText::_('CLICK_HERE_TO_CLONE_THIS_LAYOUT')?>"><?php echo JText::_('Clone')?></span>
            <?php endif; ?>
            </td>
        </tr>
        <?php $i = 1 - $i?>
        <?php }?>

        <?php $k=1?>

        <?php foreach ($layouts  as $layoutname=>$layout){?>
            <?php if($layoutname!='default'){?>
            <tr id="layout_<?php echo $layoutname?>" class="row<?php echo $i?>">
                <td width="15" style="border-left: none !important">
                    <?php echo $k+1?>
                </td>
                <td>
                    <?php echo $layoutname?>
                </td>
                <td>
                <?php if ($isNewFolderStruct): ?>
                    <span class="edit" data-layout="<?php echo $layoutname?>" title="<?php echo JText::_('CLICK_HERE_TO_EDIT_THIS_LAYOUT')?>"><?php echo JText::_('Edit')?></span>
                    <span class="clone" data-layout="<?php echo $layoutname?>" title="<?php echo JText::_('CLICK_HERE_TO_CLONE_THIS_LAYOUT')?>"><?php echo JText::_('Clone')?></span>
                    <?php if ($layout->core === null && $layout->local !== null): ?>
                        <span class="rename" data-layout="<?php echo $layoutname?>" title="<?php echo JText::_('CLICK_HERE_TO_RENAME_THIS_LAYOUT')?>"><?php echo JText::_('Rename')?></span>
                        <span class="delete" data-layout="<?php echo $layoutname?>" title="<?php echo JText::_('CLICK_HERE_TO_DELETE_THIS_LAYOUT')?>"><?php echo JText::_('Delete')?></span>
                    <?php endif; ?>
                <?php endif; ?>
                </td>
            </tr>
            <?php $k++?>
            <?php $i = 1 - $i?>
            <?php }?>
        <?php }?>

    <?php }?>
</table>
<?php if ($isNewFolderStruct): ?>
<div class="ja-layout-new" title="<?php echo JText::_('CLICK_HERE_TO_ADD_NEW_LAYOUT')?>" ><span><?php echo JText::_('New')?></span></div>
<?php endif; ?>
