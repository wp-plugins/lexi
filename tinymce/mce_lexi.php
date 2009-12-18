<?php
$wpconfig = realpath("../../../../wp-config.php");

if (!file_exists($wpconfig)) {
	echo "Could not found wp-config.php. Error in path :\n\n".$wpconfig ;	
	die;	
}// stop when wp-config is not there

require_once($wpconfig);
require_once(ABSPATH.'/wp-admin/admin.php');

// check for rights
if(!current_user_can('edit_posts')) die;

?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Lexi</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-content/plugins/lexi/tinymce/lexi.js"></script>
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
	
	<div class="panel_wrapper" style="height: 250px;">

		<!-- rss panel -->
		<div id="rss_panel" class="panel current">
		<br />
			<table border="0" cellpadding="4" cellspacing="0">
	
	
				<!--<tr>
				<td nowrap="nowrap"><label for="feedid"><?php _e("Feed:", 'lexi'); ?>:</label></td>
				<td><select id="feedtag" name="feedtag" style="width: 200px">
					<option value="0"><?php _e("All feeds", 'lexi'); ?></option>
				</td>
				</tr>-->
	
	
				<tr>
					<td nowrap="nowrap"><label for="rsslink"><?php _e("RSS", 'lexi' ); ?>:</label></td>
					<td colspan=2><input type="text" id="rsslink" name="rsslink" style="width: 200px"/></td>
				</tr>
				<tr>
					<td nowrap="nowrap" valign="top"><label><?php _e("Title", 'lexi' ); ?>:</label></td>
					<td colspan=2><input type="radio" id="rsstitle" name="group1" value="1" checked/> <?php _e("Use the title from the feed", 'lexi'); ?>
						<br><input type="radio" id="rsstitle" name="group1" value="2" onclick="document.getElementById('rssowntitle').value='';" /> <input type="text" id="rssowntitle" name="rssowntitle" style="width: 170px" value="<?php _e("Use a specific title", 'lexi'); ?>" /></td>
				</tr>
				<tr>
					<td nowrap="nowrap" valign="top"><label for="rssitems"><?php _e("Items", 'lexi' ); ?>:</label></td>
					<td colspan=2>
						<select name="rssitems" id="rssitems" style="width: 200px"><?php
							for($i=1; $i<11; $i++) {
								echo "<option value=\"$i\"";
								if ($items == $i) echo(' selected');
								echo ">$i</option>";
							} ?>
						</select>
					</td>
				</tr>
				<tr>
					<td nowrap="nowrap" valign="top"><label for="rsscache"><?php _e("Save cache", 'lexi' ); ?>: </label></td>
					<td valign="top"><input type="checkbox" id="rsscache" name="rsscache" checked /></td>
					<td><?php _e('Uncheck this option only in case the feed updates several times in an hour.','lexi'); ?></td>
				</tr>
				<tr>
					<td nowrap="nowrap"><label for="rsssc"><?php _e("Show contents", 'lexi' ); ?>:</label></td>
					<td colspan=2><input type="checkbox" id="rsssc" name="rsssc" /></td>
				</tr>
				<tr>
					<td nowrap="nowrap"><label for="rssst"><?php _e("Show feed title", 'lexi' ); ?>:</label></td>
					<td colspan=2><input type="checkbox" id="rssst" name="rssst" checked /></td>
				</tr>
				<tr>
					<td nowrap="nowrap"><label for="rsstb"><?php _e("Open links in new page", 'lexi' ); ?>:</label></td>
					<td colspan=2><input type="checkbox" id="rsstb" name="rsstb" checked /></td>
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
