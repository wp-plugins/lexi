<div class="wrap">
	<form name="form1" method="post" action="<?php echo remove_query_arg(array('mode', 'id')); ?>">
		<input type="hidden" name="mode_x" value="<?php if($mode=='add') echo "add_x"; else echo "edit_x"; ?>" />
		<input type="hidden" name="lexi_id" value="<?php echo $id; ?>" />
		<div id="icon-tools" class="icon32"><br /></div>
		<h2><?php if($mode=='add') _e( 'Add Feed', 'lexi' ); if($mode=='edit') _e( 'Edit Feed', 'lexi' );?></h2>
		<div id="poststuff" class="metabox-holder">
			<div id="post-body" class="has-sidebar">
				<div id="post-body-content" class="has-sidebar-content">
					<div id="namediv" class="stuffbox">
						<h3><label for="name"><?php _e('RSS', 'lexi'); ?></label></h3>
						<div class="inside">
							<div class="submitbox" id="submitcomment">
								<table>
									<tr>
										<td width="150px"><?php _e("Name", 'lexi' ); ?>:</td>
										<td colspan=2><input type="text" name="lexi_name" value="<?php echo $name; ?>" /></td>
									</tr>
									<tr>
										<td><?php _e("RSS", 'lexi' ); ?>:</td>
										<td colspan=2><input type="text" name="lexi_rss" value="<?php echo $rss; ?>" /></td>
									</tr>
									<tr>
										<td><?php _e("Items", 'lexi' ); ?>:</td>
										<td colspan=2>
											<select name="lexi_items"><?php 
													for($i=1; $i<11; $i++) {
														echo "<option value=\"$i\"";
														if ($items == $i) echo(' selected'); 
															echo ">$i</option>";
													} ?>
											</select>
										</td>
									</tr>
									<tr>
										<td><?php _e("Show contents", 'lexi' ); ?>:</td>
										<td width="15px"><input type="checkbox" name="lexi_showcontent" <?php if($showcontent) echo "checked"; ?>/></td>
										<td></td>
									</tr>
									<tr>
										<td><?php _e("Save cache", 'lexi' ); ?>:</td>
										<td><input type="checkbox" name="lexi_cached" <?php if($cached || !$id) echo "checked"; ?>/></td>
										<td><?php _e('Uncheck this option only in case the feed updates several times in an hour.','lexi'); ?></td>
									</tr>
								</table>
							</div>
						</div>
						<div id="major-publishing-actions">
							<div id="delete-action">
								<input type="submit" name="cancel" value="<?php _e( 'Cancel', 'lexi' );?>" class="button-primary" />
							</div>
							<div id="publishing-action" style="margin-right:18px;">
								<input type="submit" name="submit" value="<?php if($mode=='add') _e( 'Add', 'lexi' ); if($mode=='edit') _e( 'Modify', 'lexi' );?>" class="button-primary" />
							</div>
							<div class="clear"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
