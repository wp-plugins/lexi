<p>
	<label for="lexi_title">
		<?php _e('Title', 'lexi'); ?>:
		<input class="widefat" id="lexi_title" name="lexi_title" type="text" value="<?php echo $title; ?>" />
	</label>
</p>
<p>
	<label for="lexi_showtitle">
		<input type="checkbox" id="showfeedtitle" name="showfeedtitle"<?php if($show_feed_title) echo " checked"; ?>/> <?php _e('Show feed title', 'lexi'); ?>
	</label>
</p>
<p>
	<label>
		<?php printf(__('You can <a href="%s">modify</a> the feeds list in <strong>Tools/Lexi</strong>', 'lexi'), "tools.php?page=leximanage"); ?>
	</label>
</p>
<?php
if(!function_exists('minimax')) { ?>
<p>
	<label>
		<?php printf(__('You have to install <a href="%s" target="_BLANK">minimax 0.2</a> in order for this plugin to work', 'lexi'), "http://wordpress.org/extend/plugins/minimax/" ); ?>
	</label>
</p><?
} ?>
<input type="hidden" id="lexi-submit" name="lexi-submit" value="1" />
