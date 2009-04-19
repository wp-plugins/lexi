<?php
$wpconfig = realpath("../../../../wp-config.php");

if (!file_exists($wpconfig))  {
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
<body id="link" onload="tinyMCEPopup.executeOnLoad('init();');document.body.style.display='';document.getElementById('lexiid').focus();" style="display: none">
<!-- <form onsubmit="insertLink();return false;" action="#"> -->
	<form name="Lexi" action="#">
	<div class="tabs">
		<ul>
			<li id="lexi_tab" class="current"><span><a href="javascript:mcTabs.displayTab('lexi_tab','lexi_panel');" onmousedown="return false;"><?php _e("Lexi", 'lexi'); ?></a></span></li>
      <li id="rss_tab"><span><a href="javascript:mcTabs.displayTab('rss_tab','rss_panel');" onmousedown="return false;"><?php _e("RSS", 'lexi'); ?></a></span></li>
		</ul>
	</div>
	
	<div class="panel_wrapper">
		<!-- lexi panel -->
		<div id="lexi_panel" class="panel current">
		<br />
		<table border="0" cellpadding="4" cellspacing="0">
         <tr>
            <td nowrap="nowrap"><label for="lexiid"><?php _e("Feed:", 'lexi'); ?></label></td>
            <td><select id="lexiid" name="lexiid" style="width: 200px">
                <option value="0"><?php _e("All feeds", 'lexi'); ?></option>
        <?php
          $table_name = $wpdb->prefix . "lexi";
          $feeds = $wpdb->get_results("SELECT * FROM $table_name ORDER BY position ASC");
          if(is_array($feeds)) {
            foreach($feeds as $feed) {
              echo '<option value="'.$feed->id.'" >'.$feed->name.'</option>'."\n";
            }
          }
        ?>
            </select></td>
          </tr>
        </table>
		</div>

    <!-- rss panel -->
    <div id="rss_panel" class="panel">
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
          <td nowrap="nowrap"><label for="rssitems"><?php _e("Items", 'lexi' ); ?>:</label></td>
          <td colspan=2>
            <select name="rssitems" id="rssitems"><?php
                for($i=1; $i<11; $i++) {
                  echo "<option value=\"$i\"";
                  if ($items == $i) echo(' selected');
                    echo ">$i</option>";
                } ?>
            </select>
          </td>
        </tr>
        <tr>
          <td nowrap="nowrap"><label for="rsssc"><?php _e("Show contents", 'lexi' ); ?>:</label></td>
          <td colspan=2><input type="checkbox" id="rsssc" name="rsssc" /></td>
        </tr>
        <tr>
          <td nowrap="nowrap"><label for="rsscache"><?php _e("Save cache", 'lexi' ); ?>: </label></td>
          <td><input type="checkbox" id="rsscache" name="rsscache" checked /></td>
					<td rowspan=2><?php _e('Uncheck this option only in case the feed updates several times in an hour.','lexi'); ?></td>
        </tr>
				<tr>
					<td></td>
					<td></td>
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
