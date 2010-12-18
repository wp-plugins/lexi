<?php

if ( !defined('ABSPATH') )
    die('You are not allowed to call this page directly.');
    
global $wpdb;

@header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Lexi</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-content/plugins/lexi/tinymce/lexi.js?ver=<?php echo LEXI_HEADER_V; ?>"></script>
	<base target="_self" />
</head>
<body id="link" onload="tinyMCEPopup.executeOnLoad('init();');document.body.style.display='';document.getElementById('rss_tab').focus();" style="display: none">
<!-- <form onsubmit="insertLink();return false;" action="#"> -->
	<form name="Lexi" action="#">
	<div class="tabs">
		<ul>			
			<li id="rss_tab" class="current"><span><a href="javascript:mcTabs.displayTab('rss_tab','rss_panel');" onmousedown="return false;"><?php _e("Lexi Feed", 'lexi'); ?></a></span></li>
		</ul>
	</div>
	
	<div class="panel_wrapper" style="height: 255px;">

		<!-- rss panel -->
		<div id="rss_panel" class="panel current">
		<br />
			<table border="0" cellpadding="3" cellspacing="0" width="100%">
	
				<tr>
					<td nowrap="nowrap"><label for="rsslink"><?php _e("RSS", 'lexi' ); ?>:</label></td>
					<td><input type="text" id="rsslink" name="rsslink" style="width: 100%"/></td>
				</tr>
				<tr style="background: #F9F9F9;">
					<td nowrap="nowrap" valign="top"><label><?php _e("Title", 'lexi' ); ?>:</label></td>
					<td><input type="radio" id="rsstitle" name="group1" value="1" onclick="
						if(this.checked) {
							var aux = document.getElementById('rssowntitle');
							aux.disabled = true;
						}
					" checked/> <?php _e("Use the title from the feed", 'lexi'); ?>
				</tr>
				<tr style="background: #F9F9F9;">
					<td></td>
					<td><input type="radio" id="rsstitle" name="group1" value="2" onclick="
					if(this.checked) {
						var aux = document.getElementById('rssowntitle');
						aux.disabled = false;
						aux.value='';
					}
					" /> <input type="text" id="rssowntitle" name="rssowntitle" style="width: 80%" disabled value="<?php _e("Use a specific title", 'lexi'); ?>" /></td>
				</tr>
				<tr>
					<td nowrap="nowrap" valign="top"><label for="rssitems"><?php _e("Items", 'lexi' ); ?>:</label></td>
					<td>
						<input type="text" name="rssitems" id="rssitems" style="width: 30px" value="5"> <?php _e('Max number of items to show, or number of items per page when using pagination system.', 'lexi'); ?>
					</td>
				</tr>
			</table>
			<table border="0" cellpadding="2" cellspacing="0" width="100%">
			
				<tr>
					<td colspan="2" valign="top"><input type="checkbox" id="rsscache" name="rsscache" checked /> <label for="rsscache"><?php _e("Save cache", 'lexi' ); ?>. <?php _e('Uncheck this option only in case the feed updates several times in an hour.','lexi'); ?></lavel></td>
				</tr>
				<tr>
					<td nowrap="nowrap" width="50%"><input type="checkbox" id="rssst" name="rssst" checked /> <label for="rssst"><?php _e("Show feed title", 'lexi' ); ?></label></td>
					<td nowrap="nowrap" width="50%"><input type="checkbox" id="rssimg" name="rssimg" checked /> <label for="rssimg"><?php _e("Show RSS icon", 'lexi' ); ?></label></td>
				</tr>
				<tr>
					<td nowrap="nowrap"><input type="checkbox" id="rsssit" name="rsssit" checked /> <label for="rsssit"><?php _e("Show items title", 'lexi' ); ?></label></td>
					<td nowrap="nowrap"><select style="width: 160px;" id="content" name="content">
			<option value="0"><?php _e('Do not show content', 'lexi'); ?></option>
			<option value="1"><?php _e('Show content', 'lexi'); ?></option>
			<option value="2"><?php _e('Show truncated content', 'lexi'); ?></option>
			<option value="3"><?php _e('Truncated content as rollover title', 'lexi'); ?></option>
		</select></td>
				</tr>
				<tr>
					<td nowrap="nowrap"><input type="checkbox" id="rsssa" name="rsssa" /> <label for="rsssa"><?php _e("Show author", 'lexi' ); ?></label></td>
					<td nowrap="nowrap"><input type="checkbox" id="rsssd" name="rsssd" /> <label for="rsssd"><?php _e("Show date", 'lexi' ); ?></label></td>
				</tr>
				<tr>
					<td nowrap="nowrap"><input type="checkbox" id="rsstb" name="rsstb" checked /> <label for="rsstb"><?php _e("Open links in new page", 'lexi' ); ?></label></td>
					<td nowrap="nowrap"><input type="checkbox" id="rsspaginate" name="rsspaginate" /> <label for="rsspaginate"><?php _e("Paginate", 'lexi' ); ?></label></td>
				</tr>
			</table>
		</div>
	</div>

	<div class="mceActionPanel">
		<div style="float: left">
			<input type="button" id="cancel" name="cancel" value="<?php _e("Cancel", 'lexi'); ?>" onclick="tinyMCEPopup.close();" />
		</div>

		<div style="float: right">
			<input type="submit" id="insert" name="insert" value="<?php _e("Insert", 'lexi'); ?>" onclick="insertLexiLink();" />
		</div>
	</div>
</form>
</body>
</html>
