<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
$items = $this->items;
global $jacconfig;
$helper = new JACommentHelpers();
if($avatarSize == 1){
	$size = 'height:18px; width:18px;';
}else if($avatarSize == 2){
    $size = 'height:26px; width:26px;';
}else if($avatarSize == 3){
    $size = 'height:42px; width:42px;';
}
$currentCommentID = 0;
if(isset($this->currentCommentID)){
	$currentCommentID = $this->currentCommentID; 	
}
$searchItems = array();
if(isset($this->searchItems)){
	$searchItems = $this->searchItems; 	
}
$rootParentID = 0;
if(isset($this->rootParentID)){	
	$rootParentID = $this->rootParentID;	 	
}
?>
<!-- COMMENT CONTENT -->
<div class="comment-content wrap">
	<!-- START COMMENT LIST -->
	<div class="comment-listwrap">
<?php if($items) { $k = 0; ?>	
<ol class="comment-list comment-list-lv<?php echo $items[0]->level; ?>">	
	<?php foreach($items as $item): ?>
		<?php
			if($jacconfig["comments"]->get("is_show_child_comment")){
				$isShowChild = 1;
			}else{	
				$isShowChild = 0;						
				if($rootParentID != 0){				
					if(isset($searchItems[$item->id])){												
						$isShowChild = 1;										
					}
				}
			}			
		?>					
		<?php if($k % 2){$jacRow = "row0";}else{$jacRow = "row1";}?>		
		<!-- A ROW COMMENT -->
		<li id="jac-row-comment-<?php echo $item->id?>" class="jac-row-comment <?php echo $jacRow; ?> list-item <?php if($ischild){ if($isEnableThreads){ echo "comment-hasreply";}else{ echo "comment-notree";}}?> rank-high <?php if($k == 0) echo "jac-first ";if($ischild) echo " comment-replycontent";?>">
		<?php //set archo for comment?>
	    <a name="jacommentid:<?php echo $item->id;?>" href="#jacommentid:<?php echo $item->id;?>" id="jacommentid:<?php echo $item->id;?>" style="margin-top: -34px;" title=""></a>
		<div id="jac-content-of-comment-<?php echo $item->id?>" class="comment-contentmain comment-contentholder clearfix ja-imagesize<?php echo $avatarSize;?> <?php if($item->isSpecialUser){ echo " comment-byadmin";}else{if($item->isCurrentUser) echo " comment-byyou";}if($item->type == 0) echo " comment-ispending"; ?>">						
				<?php if($enableAvatar):?>					
	        <div class="avatar clearfix">                	
	            <?php if($item->strWebsite){ ?>
	            	<a href="<?php echo $item->strWebsite;?>">
						<?php if($item->avatar[0]){?>
						<img src="<?php echo $item->avatar[0];?>" alt="<?php echo $item->strUser;?>" style="<?php echo $item->avatar[1];?>"/>
						<?php }?>
						<?php if($item->icon != ''){ echo $item->icon; }?>
					</a>
	            <?php }else{ ?>
	            	<?php if($item->avatar[0]){?>
					<img src="<?php echo $item->avatar[0];?>" alt="<?php echo $item->strUser;?>" style="<?php echo $item->avatar[1];?>"/>
					<?php }?>
					<?php if($item->icon != ''){ echo $item->icon;}?>
	            <?php } ?>
	        </div>    
				<?php endif;?>				
				<div class="comment-data clearfix">
					<div class="comment-heading clearfix">						
						<?php if($item->strWebsite){ ?>
							<a href="<?php echo $item->strWebsite;?>" class="comment-user">
								<span class="comment-user"><?php echo $item->strUser; ?></span>
							</a>	         			
						<?php 
						    }else{ 
						?>                			
								<span class="comment-user"><?php echo $item->strUser; ?></span>								         			
		              <?php } ?>							
						<?php 
	            if($enableTimestamp){
				echo $helper->generatTimeStamp(strtotime($item->date));
				}else{
				echo "<span class='comment-date'>". $item->date ."</span>";
				}
	          ?>	          
	          <div class="comment-ranking" id="jac-vote-comment-<?php echo $item->id; ?>">	          	
	          	<span class="vote-comment-<?php echo $avatarSize;?> comment-rankingresult" id="voted-of-<?php echo $item->id;?>">(<?php echo $item->totalVote;?>) <?php echo JText::_("vote");?></span>
	          <?php if($item->isAllowVote){
						?>
						<div class="jac-vote-comment">																							
							<a href="javascript:voteComment(<?php echo $item->id; ?>,'up')" title="<?php echo JText::_("Vote up.");?>" class="voteup-btn hasTip"></a>
							<a href="javascript:voteComment(<?php echo $item->id; ?>,'down')" title="<?php echo JText::_("Vote down.");?>" class="votedown-btn hasTip"></a>													
						</div>	
						<?php }else{?>
						<div class="jac-vote-comment">													
							<span class="votedown-btn" title="<?php echo JText::_("Vote up.");?>">&nbsp;</span>						
							<span class="voteup-btn" title="<?php echo JText::_("Vote down.");?>">&nbsp;</span>												
						</div>
						<?php }	?>
	          </div>							
						<?php if($isAllowReport){?>					
						<div class="jac-show-report-<?php echo $avatarSize;?> comment-report" id="jac-show-report-<?php echo $item->id;?>">						
							<?php if($item->isDisableReportButton){ ?>	
								<span style="color: #A7A7A7;" class="report-btn"><?php echo JText::_("Report");?></span>															
							<?php }else{?>
								<a class="report-btn" href="javascript:reportComment(<?php echo $item->id;?>)" title="<?php echo JText::_("Flagged! it will be removed when enough people flag it.");?>"><?php echo JText::_("Report");?></a>
							<?php }?>														
						</div>
						<?php }?>
						<div class="comment-admin <?php if($item->type==1){ echo "status-isapproved";}else if($item->type==2){ echo "status-isspam";}else{ echo "status-isunapproved";}?>" id="jac-change-type-<?php echo $item->id;?>">
							<?php
								$type = $item->type;
								$itemID = $item->id;
								$parentType = $item->parentType;								
								$isAllowEditComment =  $item->isAllowEditComment; 
								include $helper->jaLoadBlock("comments/actions.php");
							?>
						</div>
					</div>
					<div id="jac-text-<?php echo $item->id;?>" class="comment-text">
						<?php																
							 $item->comment = $helper->replaceBBCodeToHTML($item->comment);								 				
		         			 echo html_entity_decode($helper -> showComment($item->comment));
		        		?> 
					</div>
					<?php //if($isAttachImage){?>
					<?php 													
						$target_path =  JPATH_ROOT.DS."images".DS."stories".DS."ja_comment".DS.$item->id;							
						//$listFiles =  $helper->files($target_path);
						$listFiles = "";
						if(is_dir($target_path))
							$listFiles  = JFolder::files($target_path);
					?>
					<?php if($listFiles){?>
					<fieldset class="fieldset legend" id="jac-attach-file-<?php echo $item->id;?>">
						
							<legend><?php echo JText::_("ATTACHED_FILE");?></legend>
							<div id="jac-list-attach-file-<?php echo $item->id;?>" class='jac-list-upload-title'>						
								<?php 																		
									foreach ($listFiles as $listFile) {
										$type = substr(strtolower(trim($listFile)), -3, 3);
										if($type=='ocx'){
						 					$type = "doc";
										}
										$linkOfFile = "index.php?tmpl=component&option=com_jacomment&view=comments&task=downloadfile&id=".$item->id."&filename=".$listFile;
						
										$_path = JPATH_BASE.DS."/components/com_jacomment/themes/" . $theme . "/images/". $type .".gif";
										if(file_exists($_path)) {
											$_link = JURI::root()."/components/com_jacomment/themes/" . $theme . "/images/". $type .".gif";
										}
										else {
											$_link = JURI::root().'templates/'.$app->getTemplate()."/html/com_jacomment/themes/" . $theme . "/images/". $type .".gif";
										}
										
										echo "<img src='". $_link . "' alt='". $listFile ."' /> <a href='". JRoute::_($linkOfFile) ."' title='". JText::_("DOWNLOAD_FILE") ."'>". $listFile ."</a><br />";
									}																
								?>
							</div>						
					</fieldset>	
				<?php }?>		
				<?php //}?>				
				</div>
				<div id="jac-div-footer-<?php echo $item->id;?>">
				<div class="comment-action clearfix">										
					<div class="comment-showreply showreply-isshowing" id="jac-div-show-child-<?php echo $item->id;?>">
						<?php if($item->children>0){?>																						
							<a href="Javascript:displayChild('<?php echo $item->id;?>')" title="<?php echo JText::_("Show all children comment of this comment.");?>" class="showreply-btn" id="a-show-childen-comment-of-<?php echo $item->id;?>" <?php if($isShowChild){?> style="display: none;"<?php }?>>
							<?php echo JText::_('Show'); ?>&nbsp;<?php 
								$item->children>1?$texReply = JText::_('Replies'):$texReply = JText::_('Reply');
								echo "<span id='jac-show-total-childen-$item->id'>". $item->children."</span> ";  
							?>								
							<?php echo $texReply; ?>
							</a>	
								
							<a href="Javascript:displayChild('<?php echo $item->id;?>')" title="<?php echo JText::_("Hide all children comment of this comment.");?>" class="hidereply-btn" id="a-hide-childen-comment-of-<?php echo $item->id;?>" <?php if(!$isShowChild){?> style="display: none;"<?php }?>>
							<?php echo JText::_('Hide'); ?>
								<?php 
									$item->children>1?$texReply = JText::_('Replies'):$texReply = JText::_('Reply');
									echo "<span id='jac-hide-total-childen-$item->id'>". $item->children."</span>";  
								?>								
							<?php echo $texReply; ?>
							</a>														
						<?php }?>
					</div>
					<div class="comment-reply" id="jac-span-reply-<?php echo $item->id;?>" >
					<?php $checkQuocte = 0;?>
					<?php if($item->type == 1){?>
					<?php if($currentUserInfo->guest && $postComment != "all"){?>
						<a href="javascript:open_login('<?php echo JText::_("Login now");?>');"><?php echo JText::_("Please login to post new comment.");?></a>
					<?php }else{ $checkQuocte = 1;?>																																			
						<a onclick="replyComment(<?php echo $item->id;?>,'<?php echo JText::_("Posting");?>','<?php echo JText::_("Reply");?>');return false;" href="#jacommentid:<?php echo $item->id;?>" id="jac-a-reply-<?php echo $item->id;?>" title="<?php echo JText::_("Reply comment");?>"><span id="reply-<?php echo $item->id;?>"><?php echo JText::_("Reply");?></span></a>																						
					<?php }?>
					<?php }?>
					</div>					
					<div class="comment-quote" id="jac-div-quote-<?php echo $item->id;?>"><?php if($checkQuocte){?><a id="jac-a-quote-<?php echo $item->id;?>" href="javascript:replyComment(<?php echo $item->id;?>,'<?php echo JText::_("Quoting");?>','<?php echo JText::_("Quote");?>','quote')" title="<?php echo JText::_("Quote this comment and reply");?>"><span id="quote-<?php echo $item->id;?>"><?php echo JText::_("Quote");?></span></a><?php }?></div>													
				</div>					
				</div>								
			<span id="jac-badge-pending-<?php echo $item->id; ?>" class="badge-pending" <?php if($item->type != 0): ?>style="display: none;"<?php endif ?>></span>
		</div>
		<div id="jac-edit-comment-<?php echo $item->id;?>" class="comment-contentmain clearfix ja-imagesize<?php echo $avatarSize;?>" style="display: none;"></div>
		<div id="jac-result-reply-comment-<?php echo $item->id;?>" class="jac-reply-comment jac-reply-level-<?php if($items[0]->level > 3){echo "3";}else{echo $items[0]->level;}?> comment-contentmain clearfix ja-imagesize<?php echo $avatarSize;?>" style="display: none;"></div>										
		<div id="childen-comment-of-<?php echo $item->id;?>" class="jac-childen-array <?php if($isShowChild){?>loaded<?php }?> <?php if(!$isEnableThreads){ echo "jac-childen-array-nothreard";}?>" <?php if(!$isShowChild){?>style="display:none;"<?php }?>>						
			<?php																	
				//display childrent item when pass. currentCommentID searchItems rootParentID;
				if($jacconfig["comments"]->get("is_show_child_comment")){
					if(isset($searchItems[$item->id]))
						echo $this->showItems($searchItems[$item->id], $searchItems, $item->id, $item->id);
				}else{
					if($isShowChild){										
						echo $this->showItems($searchItems[$item->id], $searchItems, $currentCommentID, $item->id);			
					}	
				}												
			?>
		</div>			
		<input type="hidden" id="jac-parent-of-comment-<?php echo $item->id;?>" value="<?php echo $item->parentid;?>"/>
	</li>																																					
	<!-- //A ROW COMMENT -->		
	<?php $k++;?>																													
	<?php endforeach; ?>		
</ol>				
<?php }//if Items is not null?>
	</div>
</div>
<!-- COMMENT CONTENT -->