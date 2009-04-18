<p>
	<label for="lexi_title">
		<?php _e('Title', 'lexi'); ?>:
		<input class="widefat" id="lexi_title" name="lexi_title" type="text" value="<?php echo $title; ?>" />
	</label>
</p>
<?php
if(!function_exists('minimax')) { ?>
<p>
	<label>
		<?php printf(__('You have to install <a href="%s"  target="_BLANK">minimax 0.2</a> in order for this plugin to work', 'lexi'), "http://wordpress.org/extend/plugins/minimax/" ); ?>
	</label>
</p><?
} ?>
<input type="hidden" id="lexi-submit" name="lexi-submit" value="1" />