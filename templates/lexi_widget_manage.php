<table border="0" cellpadding="4" cellspacing="4">
	<tr>
		<td width="5%"></td>
		<td></td>
		<td width="5%"></td>
		<td width="45%"></td>
	</tr>
	<tr>
		<td nowrap="nowrap"><label for="rsslink"><?php _e("RSS", 'lexi' ); ?>:</label></td>
		<td colspan="3"><input type="text" id="<?php echo $this->get_field_id('rss'); ?>" name="<?php echo $this->get_field_name('rss'); ?>" style="width: 100%" value="<?php echo $instance['rss']; ?>"/></td>
	</tr>
	<tr>
		<td nowrap="nowrap" valign="top"><label><?php _e("Title", 'lexi' ); ?>:</label></td>
		<td colspan="3"> <input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" style="width: 100%" value="<?php echo $instance['title']; ?>" /></td>
	</tr>
	<tr>
		<td nowrap="nowrap" valign="top"><label for="rssitems"><?php _e("Items", 'lexi' ); ?>:</label></td>
		<td colspan=3>
			<input type="text" name="<?php echo $this->get_field_name('items'); ?>" id="<?php echo $this->get_field_id('items'); ?>" style="width: 30px" value="<?php echo $instance['items']; ?>"> <?php _e('Max number of items to show, or number of items per page when using pagination system.', 'lexi'); ?>
		</td>
	</tr>
	<tr>
		<td valign="top" colspan="4"><input type="checkbox" id="<?php echo $this->get_field_id('use_cache'); ?>" name="<?php echo $this->get_field_name('use_cache'); ?>"<?php if((int)$instance['use_cache']) echo " checked"; ?>/> <label for="rsscache"><?php _e("Save cache", 'lexi' ); ?>. <?php _e('Uncheck this option only in case the feed updates several times in an hour.','lexi'); ?></label></td>
	</tr>
	<tr>
		<td colspan="2"><input type="checkbox" id="<?php echo $this->get_field_id('show_title'); ?>" name="<?php echo $this->get_field_name('show_title'); ?>"<?php if((int)$instance['show_title']) echo " checked"; ?>/> <label for="rssst"><?php _e("Show feed title", 'lexi' ); ?></label></td>
		<td colspan="2"><input type="checkbox" id="<?php echo $this->get_field_id('icon'); ?>" name="<?php echo $this->get_field_name('icon'); ?>"<?php if((int)$instance['icon']) echo " checked"; ?>/> <label for="rssimg"><?php _e("Show RSS icon", 'lexi' ); ?></label></td>
	</tr>
	<tr>
		<td colspan="2"><input type="checkbox" id="<?php echo $this->get_field_id('not_show_item_title'); ?>" name="<?php echo $this->get_field_name('not_show_item_title'); ?>"<?php if(!((int)$instance['not_show_item_title'])) echo " checked"; ?>/> <label for="rsstb"><?php _e("Show items title", 'lexi' ); ?></label></td>
		<td colspan="2"><input type="checkbox" id="<?php echo $this->get_field_id('show_content'); ?>" name="<?php echo $this->get_field_name('show_content'); ?>"<?php if((int)$instance['show_content']) echo " checked"; ?>/> <label for="rsssc"><?php _e("Show contents", 'lexi' ); ?></label></td>
	</tr>
	<tr>
		<td colspan="2"><input type="checkbox" id="<?php echo $this->get_field_id('show_author'); ?>" name="<?php echo $this->get_field_name('show_author'); ?>"<?php if((int)$instance['show_author']) echo " checked"; ?>/> <label for="rsssa"><?php _e("Show author", 'lexi' ); ?></label></td>
		<td colspan="2"><input type="checkbox" id="<?php echo $this->get_field_id('show_date'); ?>" name="<?php echo $this->get_field_name('show_date'); ?>"<?php if((int)$instance['show_date']) echo " checked"; ?>/> <label for="rsssd"><?php _e("Show date", 'lexi' ); ?></label></td>
	</tr>
	<tr>
		<td colspan="2"><input type="checkbox" id="<?php echo $this->get_field_id('target_blank'); ?>" name="<?php echo $this->get_field_name('target_blank'); ?>"<?php if((int)$instance['target_blank']) echo " checked"; ?>/> <label for="rsstb"><?php _e("Open links in new page", 'lexi' ); ?></label></td>
		<td colspan="2"><input type="checkbox" id="<?php echo $this->get_field_id('paginate'); ?>" name="<?php echo $this->get_field_name('paginate'); ?>"<?php if((int)$instance['paginate']) echo " checked"; ?>/> <label for="rsspaginate"><?php _e("Paginate", 'lexi' ); ?></label></td>
	</tr>
</table><?php
if(!function_exists('minimax_version') || minimax_version()<LEXI_MNMX_V) { ?>
<p>
	<label>
		<?php printf(__('You have to install <a href="%s" target="_BLANK">minimax %1.1f</a> in order for this plugin to work.', 'lexi'), "http://wordpress.org/extend/plugins/minimax/", LEXI_MNMX_V) ?>
	</label>
</p><?php
}
