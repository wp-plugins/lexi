<?php
/*
Plugin Name: Lexi
Plugin URI: http://www.sebaxtian.com/acerca-de/lexi
Description: An RSS feeder using ajax to show contents after the page has been loaded.
Version: 0.6
Author: Juan Sebastián Echeverry
Author URI: http://www.sebaxtian.com
*/

/*  Copyright 2007-2009  Juan Sebastián Echeverry  (email : sebaxtian@gawab.com)

		This program is free software; you can redistribute it and/or modify
		it under the terms of the GNU General Public License as published by
		the Free Software Foundation; either version 2 of the License, or
		(at your option) any later version.

		This program is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.

		You should have received a copy of the GNU General Public License
		along with this program; if not, write to the Free Software
		Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

$db_version=get_option('lexi_db_version');

add_action('init', 'lexi_addbuttons');
add_action('init', 'lexi_textdomain');
add_filter('the_content', 'lexi_content');
add_action('admin_menu', 'lexi_manage');
add_action('activate_lexi/lexi.php', 'lexi_activate');

/**
	* Function to use i18n
	*  
	* @access public
	*/

function lexi_textdomain() {
	load_plugin_textdomain('lexi', 'wp-content/plugins/lexi/lang');
}

/**
	* Returns plugin's path.
	*
	* @param string str Path to append
	* @return string
	* @access public
	*/

function lexi_plugin_url($str = '')
{
	$dir_name = '/wp-content/plugins/lexi';
	$url=get_bloginfo('wpurl');
	return $url . $dir_name . $str;
}

/**
	* Function to create the database and to add options into WordPress
	*
	* @access public
	*/

function lexi_activate()
{
	global $wpdb;
	global $db_version;

	$table_name = $wpdb->prefix . "lexi";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		$sql = "CREATE TABLE $table_name(
			id bigint(1) NOT NULL AUTO_INCREMENT,
			name tinytext NOT NULL,
			position int,
			rss text NOT NULL,
			items int NOT NULL,
			showcontent tinyint NOT NULL,
			cached tinyint NOT NULL,
			PRIMARY KEY (id)
		);";

		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
		dbDelta($sql);
		add_option('lexi_db_version', 1);
	}
}

/**
	* Function to edit feed's data.
	*
	* @param int id Feed's id
	* @param string name Feed's name
	* @param string rss Feed's URL
	* @param int items Items to show
	* @param bool showcontent Show contents in Widget?
	* @param bool cache Record Rss content in cache?
	* @access public
	*/

function lexi_editfeed($id, $name, $rss, $items=5, $showcontent=false, $cached=true)
{
	global $wpdb;
	
	if($showcontent) {
		$showcontent=1;
	} else {
		$showcontent=0;
	}
	
	if($cached) {
		$cached=1;
	} else {
		$cached=0;
	}
	
	$table_name = $wpdb->prefix . "lexi";
	$query="UPDATE " . $table_name ." SET name='".$name."', rss='".$rss."', items='".$items."', showcontent='".$showcontent."', cached='".$cached."' WHERE id=".$id;
	$wpdb->query($query);
}

/**
	* Function to add a feed.
	*  
	* @param string name Feed's name
	* @param string rss Feed's URL
	* @param int items Items to show
	* @param bool showcontent Show contents in Widget?
	* @param bool cache Record Rss content in cache?
	* @return bool
	* @access public
	*/

function lexi_addfeed($name, $rss, $items=5, $showcontent=false, $cached=true)
{
	global $wpdb;
	
	if($showcontent) {
		$showcontent=1;
	} else {
		$showcontent=0;
	}
	
	if($cached) {
		$cached=1;
	} else {
		$cached=0;
	}
	
	$table_name = $wpdb->prefix . "lexi";
	$insert = "INSERT INTO " . $table_name .
		" (name, rss, items, showcontent, cached) " .
		"VALUES ('" . $wpdb->escape($name) . "', '" . $wpdb->escape($rss) . "', '$items', '$showcontent', '$cached')";
	
	$results = $wpdb->query( $insert );
	
	$id = $wpdb->get_var("select last_insert_id()");
	$position = $wpdb->get_var("SELECT MAX(position) FROM $table_name");
	$position++;
	
	$wpdb->query("UPDATE " . $table_name ." SET position='$position' WHERE id=".$id);
	
	return true;
}

/**
	* Function to delete a feed.
	*
	* @param int id Feed to delete
	* @return bool
	* @access public
	*/

function lexi_deletefeed($id)
{
	global $wpdb;
	$table_name = $wpdb->prefix . "lexi";
	$position = $wpdb->get_var("SELECT position FROM $table_name WHERE id=$id");
	$query = "DELETE FROM " . $table_name ." WHERE id=" . $id;
	$answer1=$wpdb->query( $query );
	
	$wpdb->query("UPDATE " . $table_name ." SET position=position-1 WHERE position > $position");
	
	return $answer1;
}

/**
	* Move feed one row up
	*
	* @param int id Feed to move
	* @return bool
	* @access public
	*/

function lexi_upfeed($id)
{
	global $wpdb;
	$table_name = $wpdb->prefix . "lexi";
	$position = $wpdb->get_var("SELECT position FROM $table_name WHERE id=$id");
	if($position>1) {
		$position_aux=$position-1;
		$answer1 = $wpdb->query("UPDATE " . $table_name ." SET position = $position WHERE position = $position_aux");
		$answer2 = $wpdb->query("UPDATE " . $table_name ." SET position = $position_aux WHERE id = $id");
	}
	return $answer1 && $answer2;
}

/**
	* Move feed one row down
	*
	* @param int id Feed to move
	* @return bool
	* @access public
	*/

function lexi_downfeed($id)
{
	global $wpdb;
	$table_name = $wpdb->prefix . "lexi";
	$position = $wpdb->get_var("SELECT position FROM $table_name WHERE id=$id");
	$max_position = $wpdb->get_var("SELECT MAX(position) FROM $table_name");
	if($position<$max_position) {
		$position_aux=$position+1;
		$answer1 = $wpdb->query("UPDATE " . $table_name ." SET position = $position WHERE position = $position_aux");
		$answer2 = $wpdb->query("UPDATE " . $table_name ." SET position = $position_aux WHERE id = $id");
	}
	return $answer1 && $answer2;
}

/**
	* Function to open a filename. It uses CURL if the library has been installed, or
	* fopen if not.
	*
	* @param string filename File's URL
	* @return string
	* @access public
	*/

function lexi_cof_readfile($filename)
{
	$data=false;
	if(function_exists(curl_init)) {
		$ch = curl_init($filename);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$data=curl_exec($ch);
		curl_close($ch);
	} else {
		if($fop  = @fopen($filename, 'r')) {
			$data = null;
			while(!feof($fop))
				$data .= fread($fop, 1024);
			fclose($fop);
		}
	}
	return $data;
}

/**
  * Returns the HTML with all feeds.
  *
  * @return string
  * @access public
  */
  function lexi_post($id) {
    global $wpdb;

    $answer="";

    $table_name = $wpdb->prefix . "lexi";
    $feedlist = $wpdb->get_results("SELECT id FROM $table_name ORDER BY position ASC");

    // These lines generate our output. Widgets can be very complex
    // but as you can see here, they can also be very, very simple.
    if(function_exists('minimax') && minimax_version()==0.2) {
      foreach($feedlist as $feed) {
        if(!$id || $id==$feed->id) {
          $num = mt_rand();
          $url=lexi_plugin_url('/content.php')."?id=".$feed->id;
          $answer.="\n<div id='lexi$num'></div><script type='text/javascript'>mx_lexi$num = new minimax('$url', 'lexi$num');
          mx_lexi$num.get();
          </script>";
        }
      }
    } else {
    ?>
    <div id='lexi'>
      <label>
        <?php $answer=sprintf(__('You have to install <a href="%s"  target="_BLANK">minimax 0.2</a> in order for this plugin to work', 'lexi'), "http://wordpress.org/extend/plugins/minimax/" ) ?>
      </label>
    </div><?
    }
    return $answer;
  }

/**
  * Manage contents, to change [lexi].
  *
  * @access public
  */
  function lexi_content($content)
  {
    //Lexi
    $search = "@(?:<p>)*\s*\[lexi\s*(:\s*\d+)?\]\s*(?:</p>)*@i";
    if  (preg_match_all($search, $content, $matches))
    {
        if (is_array($matches))
        {
          foreach ($matches[1] as $key =>$v0)
          {
              $v1=$matches[1][$key];
              $id=false;
              if($v1) {
                $v1=substr($v1,1);
                $id=$v1*1;
              }
              
              $search = $matches[0][$key];

              $replace=lexi_post($id);

              $content = str_replace ($search, $replace, $content);
          }
        }
    }

    return $content;
  }
  
/**
  * Function to be called in templates.
  *
  * @access public
  */
  function lexi($id=false) {
    echo lexi_post($id);
  }

/**
	* Returns the HTML to show one Feed.
	*
	* @param int id Feed's id
	* @return string
	* @access public
	*/

function lexi_readfeed($id)
{
	global $wpdb;        
	$table_name = $wpdb->prefix . "lexi";
	$feed=$wpdb->get_results("SELECT name, rss, items, showcontent, cached FROM $table_name WHERE id=$id");
	$feed=$feed[0];
	$data=false;

	include_once(ABSPATH . WPINC . '/rss.php');
	if($feed->cached) {
		$rss = fetch_rss($feed->rss);
		$items = array_slice($rss->items, 0, $feed->items);
		
		$answer = "<h2 class='widgettitle'><a class='rsswidget' href='".$feed->rss."' title='" . __('Subscribe' , 'lexi')."'><img style='background:orange;color:white;border:none;' width='14' height='14' src='".get_bloginfo('wpurl')."/wp-includes/images/rss.png' alt='RSS' /></a> <a class='rsswidget' href='".htmlspecialchars($rss->channel['link'])."' title='".$feed->name."'>".$feed->name."</a></h2><ul>";
		if($items){
			foreach ($items as $item) {
				$answer.="<li><a class='rsswidget' href='".htmlspecialchars($item['link'])."' target='_blank'>".$item['title']."</a>";
								if($feed->showcontent) $answer.="<br/>".$item['description'];
								$answer.="</li>";
			}
		}
		$answer.="</ul>";
	} else {
		$data = lexi_cof_readfile($feed->rss);
	}

	if($data)
	{
		$rss = new SimpleXMLElement($data);
			
		$answer = "<h2 class='widgettitle'><a class='rsswidget' href='".$feed->rss."' title='Suscribirse a este contenido'><img style='background:orange;color:white;border:none;' width='14' height='14' src='".get_bloginfo('wpurl')."/wp-includes/images/rss.png' alt='RSS' /></a> <a class='rsswidget' href='".$rss->channel->link."' title='".$feed->name."'>".$feed->name."</a></h2><ul>";
		$count=0;
		if($rss->channel->item){
						foreach ($rss->channel->item as $item) {
									if($count<$feed->items) {
														$answer.="<li><a class='rsswidget' href='".$item->link."' target='_blank'>".$item->title."</a>";
														if($feed->showcontent) $answer.="<br/>".$item->description;
														$answer.="</li>";
									}
									$count++;
						}
		}
		$answer.="</ul>";
	}
	return $answer;
}

/**
	* Enable menu to manage Feeds.
	*
	* @access public
	*/

function lexi_manage()
{
	add_management_page('Lexi', 'Lexi', 10, 'leximanage', 'lexi_manage_page');
}

/**
	* Page to manage feeds.
	*  
	* @access public
	*/

function lexi_manage_page()
{
	global $wpdb;
	
	$table_name = $wpdb->prefix . "lexi";
	$messages=array();
	
	if(!function_exists('minimax')) { 
		array_push($messages, sprintf(__('You have to install <a href="%s"  target="_BLANK">minimax 0.2</a> in order for this plugin to work', 'lexi'), "http://wordpress.org/extend/plugins/minimax/" ));
	}

	$mode_x=$_POST['mode_x']; // Something from POST
	$mode=$_GET['mode']; // Something from GET?
	
	//if pressed addfeed, mode must be add feed
	if($_POST['addfeed']) {
		$mode='add';
		$mode_x='done';
	}
	
	$doaction=false;
	if($_POST['doaction']!="") $doaction=$_POST['action'];
	if($_POST['doaction2']!="") $doaction=$_POST['action2'];
	if($doaction)
	{
		switch($doaction)
		{
			case 'delete':
				foreach($_POST['checked_feeds'] as $checked_id) {
					lexi_deletefeed($checked_id);
				}
				break;
		}
	}
	
	switch($mode_x) {
		case 'manage_x':
			$mode='done';
			break;
		case 'add_x':
			$mode='done';
			if($_POST['submit']) {
				$name=$_POST['lexi_name'];
				$rss=$_POST['lexi_rss'];
				$items=$_POST['lexi_items'];
				$showcontent=false;
				if($_POST['lexi_showcontent']=='on') {
					$showcontent=true;
				}
				$cached=false;
				if($_POST['lexi_cached']=='on') {
					$cached=true;
				}
				lexi_addfeed($name, $rss, $items, $showcontent, $cached);
				array_push($messages, __( 'Feed added', 'lexi' ));
			}
			break;
		case 'edit_x':
			$mode='done';
			if($_POST['submit']) {
				$id=$_POST['lexi_id'];
				$name=$_POST['lexi_name'];
				$rss=$_POST['lexi_rss'];
				$items=$_POST['lexi_items'];
				$showcontent=false;
				if($_POST['lexi_showcontent']=='on') {
					$showcontent=true;
				}
				$cached=false;
				if($_POST['lexi_cached']=='on') {
					$cached=true;
				}
				lexi_editfeed($id, $name, $rss, $items, $showcontent, $cached);
				array_push($messages, __( 'Feed modified', 'lexi' ));
			}
			break;
	}
	
	switch($mode) {
		case 'add':
			include('templates/lexi_feed.php');
			break;
		case 'edit':
			check_admin_referer('lexi_editfeed');
			$id=$_GET['id'];
			$table_name = $wpdb->prefix . "lexi";
			$data = $wpdb->get_row("select name, rss, items, showcontent, cached from $table_name where id=$id");
			$name=$data->name;
			$rss=$data->rss;
			$items=$data->items;
			$cached=$data->cached;
			$showcontent=$data->showcontent;
			include('templates/lexi_feed.php');
			break;
		case 'up':
			check_admin_referer('lexi_upfeed');
			$id=$_GET['id'];
			if(lexi_upfeed($id)) {
				array_push($messages, __("Feed moved", 'lexi'));
			}
			break;
		case 'down':
			check_admin_referer('lexi_downfeed');
			$id=$_GET['id'];
			if(lexi_downfeed($id)) {
				array_push($messages, __("Feed moved", 'lexi'));
			}
			break;
		case 'delete':
			check_admin_referer('lexi_deletefeed');
			$id=$_GET['id'];
			if(lexi_deletefeed($id)) {
				array_push($messages, __("Feed deleted", 'lexi'));
			}
			break;
	}
	
	if($mode!='edit' && $mode!='add')
	{
		// Now display the manage screen			
		include('templates/lexi_manage.php');
	}
}

function lexi_addbuttons() {
   // Don't bother doing this stuff if the current user lacks permissions
      if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) return;

   // Add only in Rich Editor mode
        if ( get_user_option('rich_editing') == 'true') {

      // add the button for wp21 in a new way
         add_filter('mce_external_plugins',  'add_lexi_script');
         add_filter('mce_buttons',  'add_lexi_button');
      }
}

function add_lexi_button($buttons) {

   array_push($buttons, 'Lexi');
   return $buttons;

}

function add_lexi_script($plugins) {
   $dir_name = '/wp-content/plugins/lexi';
   $url=get_bloginfo('wpurl');
   $pluginURL =  $url.$dir_name.'/tinymce/editor_plugin.js';
   $plugins['Lexi'] = $pluginURL;
   return $plugins;
}


// lexi widget stuff
function lexi_widget_init() {

	if ( !function_exists('register_sidebar_widget') ) {
		return;
	}

	function lexi_widget($args) {

		global $wpdb;
		
		// $args is an array of strings that help widgets to conform to
		// the active theme: before_widget, before_title, after_widget,
		// and after_title are the array keys. Default tags: li and h2.
		extract($args);

		//$table_name = $wpdb->prefix . "lexi";
		//$feedlist = $wpdb->get_results("SELECT id FROM $table_name ORDER BY position ASC");
		
		// These lines generate our output. Widgets can be very complex
		// but as you can see here, they can also be very, very simple.
		echo $before_widget;
    lexi();
		echo $after_widget;
	}
	
	// This registers our widget so it appears with the other available
	// widgets and can be dragged and dropped into any active sidebars.
	register_sidebar_widget(array('Lexi RSS Widget', 'widgets'), 'lexi_widget');

}

// Run our code later in case this loads prior to any required plugins.
add_action('widgets_init', 'lexi_widget_init');

?>