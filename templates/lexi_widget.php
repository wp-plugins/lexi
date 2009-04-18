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
		<?php _e('You have to install <a href="http://www.sebaxtian.com/acerca-de/minimax"  target="_BLANK">minimax</a> in order for this plugin to work', 'lexi'); ?>
	</label>
</p><?
} ?>
<input type="hidden" id="lexi-submit" name="lexi-submit" value="1" />