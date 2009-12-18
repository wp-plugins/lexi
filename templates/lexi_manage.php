<?php
	global $wp_query;

	//Actions messages
	if(count($messages)>0) {
		echo "<div class='updated'>";
		foreach($messages as $message) {
			echo "<p><strong>$message</strong></p>";
		}
		echo "</div>";
	}
	
?>
<div class="wrap">
	<div id="icon-tools" class="icon32"><br /></div>
	<h2><?php _e( 'Lexi', 'lexi' ); ?></h2>
	<form name="form1" method="post" action="<?php echo add_query_arg(array('mode'=>'', 'text'=>$text)); ?>"><?
		$feedlist = $wpdb->get_results("SELECT * FROM $table_name ORDER BY position ASC");
		$max_position = $wpdb->get_var("SELECT MAX(position) FROM $table_name");
		$count=count($feedlist);
		if($count==0) { ?>
		<div class="clear"></div>
		<p><?php _e('No feeds found', 'lexi') ?> <input type="submit" value="<?php _e( 'Add Feed', 'lexi' ); ?>" class="button" name="addfeed" /></p><?php 
		} else { ?>
		<div class="tablenav">
			<div class="alignleft actions">
				<select name="action">
					<option value="-1" selected="selected"><?php _e('Bulk Actions'); ?></option>
					<option value="delete"><?php _e('Delete'); ?></option>
				</select>
				<input type="submit" name="doaction" id="doaction" value="<?php _e('Apply'); ?>" class="button-secondary apply" />
				<input type="hidden" name="_wp_http_referer" value="/wordpress/wp-admin/edit-feeds.php" />
				<input type="submit" value="<?php _e( 'Add Feed', 'lexi' ); ?>" class="button" name="addfeed" />
			</div>
			<br class="clear" />
		</div>
		<div class="clear"></div>
		<table class="widefat feeds fixed" cellspacing="0">
			<thead>
				<tr>
					<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
					<th scope="col" width="90%" style=""><?php _e( 'Feed' , 'lexi'); ?></th>
					<th scope="col" width="80px" style=""><div align="center"><?php _e( 'Show contents' , 'lexi'); ?></div></th>
					<th scope="col" width="80px" style=""><div align="center"><?php _e( 'Save cache' , 'lexi'); ?></div></th>
				</tr>
			</thead>
			<tbody id="the-feed-list" class="list:feed"><?
				foreach($feedlist as $feed) { ?>
				<tr id='feed-<?php echo $feed->id; ?>'>
					<th scope="row" class="check-column"><input type='checkbox' name='checked_feeds[]' value='<?php echo $feed->id; ?>' /></th>
					<td>
						<strong><?php echo $feed->name." (".$feed->items.")"; ?></strong><br /><?php echo $feed->ip; ?>
						<div class="row-actions">
							<span><a href="<?php echo add_query_arg( array('mode' => 'edit', 'id' => $feed->id) ); ?>" class='edit'><?php _e('Edit', 'lexi') ?></a></span>
							<span class='delete'> | <a href="<?php echo add_query_arg( array('mode' => 'delete', 'id' => $feed->id) ); ?>" class="delete" onclick="javascript:check=confirm( '<?php _e("Delete this Feed?",'lexi')?>');if(check==false) return false;"><?php _e('Delete', 'lexi') ?></a></span>
							<span class='edit'><?php if($feed->position!=1) {?> | <a href="<?php echo add_query_arg( array('mode' => 'up', 'id' => $feed->id) ); ?>" class='edit'> <?php echo "<img src='../wp-content/plugins/lexi/img/up.png' border='0'>"; ?></a><?php } ?></span>
							<span class='edit'><?php if($feed->position!==$max_position) { ?> | <a href="<?php echo add_query_arg( array('mode' => 'down', 'id' => $feed->id) ); ?>" class='edit'> <?php echo "<img src='../wp-content/plugins/lexi/img/down.png' border='0'>"; ?></a><?php } ?></span>
						</div>
					</td>
					<td class="feed column-feed"><div align="center"><?php if($feed->showcontent) echo "<img src='../wp-content/plugins/lexi/img/yes.png'>"; else echo "<img src='../wp-content/plugins/lexi/img/no.png'>";?></div></td>
					<td><div align="center"><?php if($feed->cached) echo "<img src='../wp-content/plugins/lexi/img/yes.png'>"; else echo "<img src='../wp-content/plugins/lexi/img/no.png'>";?></div></td>
				</tr><?php 
					} ?>
			</tbody>
		</table>
		<div class="tablenav">
			<div class="alignleft actions">
				<select name="action2">
					<option value="-1" selected="selected"><?php _e('Bulk Actions'); ?></option>
					<option value="delete"><?php _e('Delete'); ?></option>
				</select>
				<input type="submit" name="doaction2" id="doaction2" value="<?php _e('Apply'); ?>" class="button-secondary apply" />
				<input type="hidden" name="_wp_http_referer" value="/wordpress/wp-admin/edit-feeds.php" />
				<input type="submit" value="<?php _e( 'Add Feed', 'lexi' ); ?>" class="button" name="addfeed" />
			</div>
			<br class="clear" />
		</div><?php 
			} ?>
	</form>
</div>
