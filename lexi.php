<?php
/*
Plugin Name: Lexi
Plugin URI: http://www.sebaxtian.com/acerca-de/lexi
Description: An RSS feeder using ajax to show contents after the page has been loaded.
Version: 0.8.4
Author: Juan Sebastián Echeverry
Author URI: http://www.sebaxtian.com
*/

/* Copyright 2007-2009 Juan Sebastián Echeverry (email : sebaxtian@gawab.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

define('CONF_CACHE', 1);
define('CONF_SHOWCONTENT', 2);
define('CONF_SHOWHEADER', 4);
define('CONF_TARGETBLANK', 8);
define('CONF_NOTSHOWICON', 16);
define('LEXI_LIST', -1);

$db_version=get_option('lexi_db_version');

add_action('init', 'lexi_add_buttons');
add_action('init', 'lexi_text_domain');
add_action('wp_head', 'lexi_header');
add_filter('the_content', 'lexi_content');
add_action('admin_menu', 'lexi_manage');
add_action('activate_lexi/lexi.php', 'lexi_activate');

/**
* To declare where are the mo files (i18n).
* This function should be called by an action.
*
* @access public
*/
function lexi_text_domain() {
	load_plugin_textdomain('lexi', 'wp-content/plugins/lexi/lang');
}

/**
* Function to add the required data to the header in the site.
* This function should be called by an action.
*
* @access public
*/
function lexi_header() {
	$css = get_theme_root()."/".get_template()."/lexi.css";
	if(file_exists($css)) {
		echo "<link rel='stylesheet' href='".get_bloginfo('template_directory')."/lexi.css' type='text/css' media='screen' />";
	} else {
		echo "<link rel='stylesheet' href='".lexi_plugin_url("/css/lexi.css")."' type='text/css' media='screen' />";
	}
}


/**
* Function to return the url of the plugin concatenated to a string. The idea is to
* use this function to get the entire URL for some file inside the plugin.
*
* @access public
* @param string str The string to concatenate
* @return The URL of the plugin concatenated with the string 
*/
function lexi_plugin_url($str = '') {

	$aux = '/wp-content/plugins/lexi/'.$str;
	$aux = str_replace('//', '/', $aux);
	$url = get_bloginfo('wpurl');
	return $url.$aux;

}


/**
* Function to create the database and to add options into WordPress
* This function should be called by an action.
*
* @access public
*/
function lexi_activate() {
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
	
	//Create the cache directory if it doesn't exist
	$ans = parse_url(get_bloginfo('wpurl'));
	$cache_dir = $_SERVER['DOCUMENT_ROOT'].$ans['path'].'/wp-content/cache';
	
	if(!file_exists($cache_dir)) mkdir($cache_dir);
	if(!file_exists($cache_dir.'/lexi')) mkdir($cache_dir.'/lexi');
	
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
function lexi_edit_feed($id, $name, $rss, $items=5, $showcontent=false, $cached=true) {
	global $wpdb;

	// To send numbers instead of booleans in the SQL query
	if($showcontent) $showcontent=1; else $showcontent=0;
	if($cached) $cached=1; else $cached=0;

	// Set the new data in the database
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
function lexi_add_feed($name, $rss, $items=5, $showcontent=false, $cached=true) {
	global $wpdb;

	// To send numbers instead of booleans in the SQL query
	if($showcontent) $showcontent=1; else $showcontent=0;
	if($cached) $cached=1; else $cached=0;

	// Add the new feed to the database
	$table_name = $wpdb->prefix . "lexi";
	$insert = "INSERT INTO " . $table_name .
		" (name, rss, items, showcontent, cached)" .
		" VALUES ('" . $wpdb->escape($name) . "', '" . 
		$wpdb->escape($rss) . "', '$items', '$showcontent', '$cached')";

	$results = $wpdb->query( $insert );

	// Get the id assigned to the feed.
	$id = $wpdb->get_var("select last_insert_id()");
	
	// Get the last position
	$position = $wpdb->get_var("SELECT MAX(position) FROM $table_name");
	$position++;

	// Set the feed to the end of the list
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
function lexi_delete_feed($id) {
	global $wpdb;
	$table_name = $wpdb->prefix . "lexi";
	
	// Get the position of the feed to be deleted
	$position = $wpdb->get_var("SELECT position FROM $table_name WHERE id=$id");
	
	// Delete the feed
	$query = "DELETE FROM " . $table_name ." WHERE id=" . $id;
	$answer1=$wpdb->query( $query );

	// Move up all the feeds after the deleted one 
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
function lexi_up_feed($id) {
	global $wpdb;
	$table_name = $wpdb->prefix . "lexi";
	
	// Get the position
	$position = $wpdb->get_var("SELECT position FROM $table_name WHERE id=$id");
	
	// If the position is greater than 1 (it is not the first in the list), move the feed
	if($position>1) {
		// Change positions with the feed to move up and the feed that is one row over.
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
function lexi_down_feed($id) {
	global $wpdb;
	$table_name = $wpdb->prefix . "lexi";
	
	// Get the position and the bigest position we have
	$position = $wpdb->get_var("SELECT position FROM $table_name WHERE id=$id");
	$max_position = $wpdb->get_var("SELECT MAX(position) FROM $table_name");
	// If the position we have to move is lesser than the last position, move the feed
	if($position<$max_position) {
		// Change positions with the feed to move down and the feed that is in the next row .
		$position_aux=$position+1;
		$answer1 = $wpdb->query("UPDATE " . $table_name ." SET position = $position WHERE position = $position_aux");
		$answer2 = $wpdb->query("UPDATE " . $table_name ." SET position = $position_aux WHERE id = $id");
	}
	return $answer1 && $answer2;
}


/**
* Returns the html code with 'minimax' script and div.
* Add this code into the html page to create the RSS reader.
*
* @param string link The RSS URL
* @param string title The title to put at the beginning of the list
* @param string items Max number of feeds to show
* @param int conf The configuration number, see function description
* @return string
* @access public
*/
function lexi_viewer_rss($link, $title, $items, $conf) {
	$answer="";
	
	// If we have minimax, go ahead
	if(function_exists('minimax_version') && minimax_version()>=0.3) {
		$num = mt_rand();
		$url=lexi_plugin_url('/ajax/content.php');
		$nonce = wp_create_nonce('lexi');
		// Create the post to ask for the rss feeds
		$post="nonce=$nonce&amp;url=".urlencode(str_replace("&amp;", "&", $link))."&amp;title=".urlencode(str_replace("&amp;", "&", $title))."&amp;num=$items&amp;conf=$conf";
		// Create the div where we want the feed to be shown, and the instance of minimax
		$answer.="\n<div id='lexi$num' class='lexi'><table><tr><td><img class='lexi' src='".get_bloginfo('wpurl')."/wp-content/plugins/lexi/img/loading.gif' alt='RSS' border='0' /></td><td>".__('Loading Feed...','lexi')."</td></tr></table></div><script type='text/javascript'>mx_lexi$num = new minimax('$url', 'lexi$num');
		mx_lexi$num.post('$post');
		</script>";
	} else { // If minimax isn't installed, ask for it to the user
		$answer.= "<div id='lexi'><label>";
		$answer.= sprintf(__('You have to install <a href="%s" target="_BLANK">minimax 0.3</a> in order for this plugin to work', 'lexi'), "http://wordpress.org/extend/plugins/minimax/" );
		$answer.= "</label></div>";
	}
	return $answer;
}


/**
* Returns the html code with 'minimax' script and div, to show one feed from the internal list.
* Add this code into the html page to create the RSS reader.
*
* @param int id The feed Id into lexi list.
* @return string
* @access public
*/
function lexi_viewer_id($id) {
	global $wpdb;

	// Suppose we have to show the title from the feed
	$show_feed_title = true;

	// If we have to show the entire list (maybe the widget) use the options in the widget
	if($id==LEXI_LIST) {
		$options = get_option('widget_lexi');
		$show_feed_title = $options['show_feed_title'];
		$id=0;
	}

	$answer="";
	$table_name = $wpdb->prefix . "lexi";

	// If we have to show one feed, get it
	if($id)
		$feedlist = $wpdb->get_results("SELECT * FROM $table_name WHERE id = $id");
	else // Else, get all the feeds order by position
		$feedlist = $wpdb->get_results("SELECT * FROM $table_name ORDER BY position ASC");

	// If we have minimax installed, go ahead end create the script to get the feeds
	if(function_exists('minimax_version') && minimax_version()>=0.3) {
		// We can have one feed or the list
		foreach($feedlist as $feed) {
			// Sets the configuration
			$conf = CONF_TARGETBLANK;
			if($feed->showcontent) $conf += CONF_SHOWCONTENT;
			if($feed->cached) $conf += CONF_CACHE;
			if($show_feed_title) $conf += CONF_SHOWHEADER;

			// Show the feed, using the data and configuration
			$answer.= lexi_viewer_rss($feed->rss,$feed->name,$feed->items,$conf);
		}
	} else { // if we don't have minimax, ask the user for it
		$answer.= "<div id='lexi'><label>";
		$answer.= sprintf(__('You have to install <a href="%s" target="_BLANK">minimax 0.3</a> in order for this plugin to work', 'lexi'), "http://wordpress.org/extend/plugins/minimax/" );
		$answer.= "</label></div>";
	}
	return $answer;
}


/**
* Filter to manage contents. Check for [lexi] tags.
* This function should be called by a filter.
*
* @access public
* @param string content The content to change.
* @return The content with the changes the plugin have to do.
*/
function lexi_content($content) {
	//Show a feed from the internal list
	$search = "@(?:<p>)*\s*\[lexi\s*(:\s*\d+)?\]\s*(?:</p>)*@i";
	if(preg_match_all($search, $content, $matches)) {
		if(is_array($matches)) {
			foreach($matches[1] as $key =>$v0) {
				// Get the data from the tag
				$v1=$matches[1][$key];
				$id=false;
				if($v1) {
					$v1=substr($v1,1);
					$id=$v1*1;
				}
				
				$search = $matches[0][$key];
				// Create the scripts to show the feed
				$replace=lexi_viewer_id($id);
				$content = str_replace ($search, $replace, $content);
			}
		}
	}
	
	//Show a specific feed (Lexi 1)
	$search = "@(?:<p>)*\s*\[lexi\s*:([^,]+),(\d+),(true|false),(true|false)?\]\s*(?:</p>)*@i";
	if(preg_match_all($search, $content, $matches)) {
		if(is_array($matches)) {
			foreach($matches[1] as $key =>$rss) {
				// Get the data from the tag
				$items=$matches[2][$key];
				$sc=$matches[3][$key];
				if($sc=='true') $sc=1; else $sc=0;
				$cache=$matches[4][$key];
				if($cache=='true') $cache=1; else $cache=0;
	
				// Calculate the configuration number from the tag data
				$conf = CONF_SHOWHEADER + CONF_TARGETBLANK;
				if($sc) $conf += CONF_SHOWCONTENT;
				if($cache) $conf += CONF_CACHE;
	
				$search = $matches[0][$key];
				// Create the script to show the feed
				$replace=lexi_viewer_rss($rss,"",$items,$conf);
				$content = str_replace ($search, $replace, $content);
			}
		}
	}
	
	//Show a specific feed, with title (Lexi 2 - using conf number)
	$search = "@(?:<p>)*\s*\[lexi\s*:(\d+),([^,]+),([^,]+),(\d+)?\]\s*(?:</p>)*@i";
	if(preg_match_all($search, $content, $matches)) {
		if(is_array($matches)) {
			foreach($matches[1] as $key =>$conf) {
				// Get data from tag
				$rss=$matches[2][$key];
				$title=$matches[3][$key];
				$items=$matches[4][$key];
				
				$search = $matches[0][$key];
				// Create the script to show the feed
				$replace=lexi_viewer_rss($rss, $title, $items, $conf);
				$content = str_replace ($search, $replace, $content);
			}
		}
	}
	
	//Show a specific feed, without title (Lexi 2 - using conf number)
	$search = "@(?:<p>)*\s*\[lexi\s*:(\d+),([^,]+),(\d+)?\]\s*(?:</p>)*@i";
	if(preg_match_all($search, $content, $matches)) {
		if(is_array($matches)) {
			foreach($matches[1] as $key =>$conf) {
				// Get data from tag
				$rss=$matches[2][$key];
				$items=$matches[3][$key];
				
				$search = $matches[0][$key];
				// Create the script to show the feed
				$replace=lexi_viewer_rss($rss, "", $items, $conf);
				$content = str_replace ($search, $replace, $content);
			}
		}
	}
	
	return $content;
}

/**
* Function to be called in templates. Returns the html code
* with 'minimax' script and div. Add this function into the html
* page to create the RSS reader.
* If the id is numeric, the function returns the code for the
* corresponding feed in the lexi list, and forget the other
* parameters. If its a string, the function would use it as the URL,
* and would use the other parameters.
* If you call the function without parameters, it returns the code
* for the entire lexi list.
*
* @param string id Would be the URL or the lexi id. See function description.
* @param string num Max number of feeds to show
* @param string sc Show feed contents?
* @param string cached Save feeds in cache?
* @access public
*/
function lexi($id=0, $num=0, $sc=false, $cached=false, $sh=true) {
	// If it is just the id, call with the default values
	if(is_numeric($id)) {
		echo lexi_viewer_id($id);
	} else { // else, call with the specific values
		echo lexi_viewer_rss($id, "", $num, $sc, $cached, $sh);
	}
}

/**
* Function to be called in templates. Returns the html code
* with 'minimax' script and div. Add this function into the html
* page to create the RSS reader.
* The configuration number can be calculated:
*		add 1 if the feed will be saved in cache 
*		add 2 if lexi has to show the content
*		add 4 if you want to show the title at thee begin of the list
*		add 8 if you want the link for each feed to open in a new window.
*
* @param int conf The configuration number, see function description.
* @param string rss The URL of the feed to show
* @param string title The title to use, if empty will use the name of the feed
* @param string max_items Max number of feeds to show
* @access public
*/
function lexiRss($conf, $rss, $title, $max_items) {
	if(!$title) $title="";
	echo lexi_viewer_rss($rss, $title, $max_items, $conf); 
}


/**
* Returns the HTML list for an RSS feed.
* The configuration number can be calculated:
*		add 1 if the feed will be saved in cache 
*		add 2 if lexi has to show the content
*		add 4 if you want to show the title at thee begin of the list
*		add 8 if you want the link for each feed to open in a new window.
*
* @param string link The URL of the rss.
* @param string name Name to be shown at the top of the list. If empty would use
* the name in the rss.
* @param string num Max number of feeds to show.
* @param int conf The config number, see function description.
* @return string
* @access public
*/
function lexi_read_feed($link, $name, $num, $config) {
	//Use the rss libraries in WP.
	include_once(ABSPATH . WPINC . '/rss.php');
	@include_once(ABSPATH . WPINC . '/class-simplepie.php');
	
	//Get cache directory
	$ans = parse_url(get_bloginfo('wpurl'));
	$cache_dir = $_SERVER['DOCUMENT_ROOT'].$ans['path'].'/wp-content/cache/lexi/';
	
	// As this data come from a POST, fix the situation with the dobled quoted strings 
	$name=str_replace("\\\"","\"",$name);

	// Do we have to open links in new pages?
	if(($config & CONF_TARGETBLANK)) {
		$target = " target='_blank'";
	}
		
	// Does simplepie library exists?
	if(class_exists('SimplePie')) {
		//Get the data from the rss
		$rss = new SimplePie();
		
		//Set cache dirname
		$rss->set_cache_location($cache_dir);
		
		//Set the feed url
		$rss->set_feed_url($link);

		//Do we have to disable cache?
		if(!($config & CONF_CACHE)) $rss->enable_cache(false);
		
		//Get the feed
		$rss->init();
		
		//Ǵet the link to the page (not to the RSS) from the feed
		$channel_link = $rss->get_permalink();
		
		//If we don't have a title, use the name from the feed
		if($name=="") {
			$name=htmlspecialchars($rss->get_title());
		}
		
		//Get the items to show
		$items = $rss->get_items(0, $num);
		
		//If we have something to show, show the items
		if($items) {
			foreach($items as $item) {
				//Every feed is an item in the list.
				//In this link we use:
				//		link to the feed
				//		target to define if we need to open the link in a ned window
				//		title of the feed
				//		the content and the variable to know if we have to show it
				$answer.="<li><a class='rsswidget' href='".htmlspecialchars($item->get_permalink())."'".$target.">".$item->get_title()."</a>";
				if($config & CONF_SHOWCONTENT) $answer.="<br/>".$item->get_content();
				$answer.="</li>";
			}
		}
		
	} else { //We don't have simplepie, try with MAGPIE
		//Set the new cache dir
		
		define('MAGPIE_CACHE_DIR', $cache_dir);
		
		//Do we have to save in cache?
		if(!($config & CONF_CACHE)) {
			define('MAGPIE_CACHE_ON', 0);
		}
		
		//Start the rss conection
		$rss = fetch_rss($link);
		
		//Ǵet the link to the page (not to the RSS) from the feed
		$channel_link=htmlspecialchars($rss->channel['link']);
		
		//If we don't have a title, use the name from the feed
		if($name=="") {
			$name=htmlspecialchars($rss->channel['title']);
		}
		
		//Get the items to show
		$items = array_slice($rss->items, 0, $num);
		
		//If we have something to show, show the items
		if($items) {
			foreach($items as $item) {
				//Every feed is an item in the list.
				//In this link we use:
				//		link to the feed
				//		target to define if we need to open the link in a ned window
				//		title of the feed
				//		the content and the variable to know if we have to show it
				$answer.="<li><a class='rsswidget' href='".htmlspecialchars($item['link'])."'".$target.">".$item['title']."</a>";
				if($config & CONF_SHOWCONTENT) $answer.="<br/>".$item['atom_content'].$item['summary'];
				$answer.="</li>";
			}
		}
	}
	
	//The default image and its link
	$img = "<a class='rsswidget' href='$link' title='" . __('Subscribe' , 'lexi')."'><img class='lexi' src='".get_bloginfo('wpurl')."/wp-includes/images/rss.png' alt='RSS' border='0' /></a> ";
	//Show the image?
	if($config & CONF_NOTSHOWICON) {
		$img = ""; //We don't have to.
	}

	
	$header="";
	//If we need a title
	if($config & CONF_SHOWHEADER) {
		$header = "<h2 class='widgettitle'>$img<a class='rsswidget' href='$channel_link' title='$name'>$name</a></h2>";
	}
	
	// Return the list of linked feeds
	return "$header<ul>$answer</ul>";
}


/**
* Enable menu to manage Feeds.
* This function should be called by an action.
*
* @access public
*/

function lexi_manage() {
	add_management_page('Lexi', 'Lexi', 10, 'leximanage', 'lexi_manage_page');
}


/**
* Page to manage feeds.
*
* @access public
*/

function lexi_manage_page() {
	global $wpdb;

	$table_name = $wpdb->prefix . "lexi";
	$messages=array();
	
	//If we don't have minimax, ask the user for it
	if(!function_exists('minimax_version') || minimax_version()<0.3) { 
		array_push($messages, sprintf(__('You have to install <a href="%s" target="_BLANK">minimax 0.3</a> in order for this plugin to work', 'lexi'), "http://wordpress.org/extend/plugins/minimax/" ));
	}

	$mode_x=$_POST['mode_x']; // Something from POST
	$mode=$_GET['mode']; // Something from GET?

	//if pressed addfeed, mode must be add feed
	if($_POST['addfeed']) {
		$mode='add';
		$mode_x='done';
	}

	// Assume we don't have to do any action, but ask if we have
	$doaction=false;
	if($_POST['doaction']!="") $doaction=$_POST['action'];
	if($_POST['doaction2']!="") $doaction=$_POST['action2'];
	
	//In case we have to do something previous
	if($doaction) {
		switch($doaction) {
			case 'delete': //if the action ask to delete a feed from the list
				foreach($_POST['checked_feeds'] as $checked_id) {
					lexi_delete_feed($checked_id);
				}
				break;
		}
	}
	
	//if we are going to execute a command
	switch($mode_x) {
		case 'manage_x': //Just show the internal list and the items to manage it
			$mode='done';
			break;
		case 'add_x': //Add a feed
		case 'edit_x': //Edit a feed
			$mode='done';
			if($_POST['submit']) {
				//Get the data from the form
				$name=$_POST['lexi_name'];
				$rss=$_POST['lexi_rss'];
				$items=$_POST['lexi_items'];
				if(!is_numeric($items)) $items=5;
				$showcontent=false;
				if($_POST['lexi_showcontent']=='on') $showcontent=true;
				$cached=false;
				if($_POST['lexi_cached']=='on') $cached=true;
			
				if($mode_x=='add_x') {
					//Add the new feed
					lexi_add_feed($name, $rss, $items, $showcontent, $cached);
					//The message to say that we added a feed
					array_push($messages, __( 'Feed added', 'lexi' ));
				}
				
				if($mode_x=='edit_x') {
					$id=$_POST['lexi_id'];
					//Edit the feed
					lexi_edit_feed($id, $name, $rss, $items, $showcontent, $cached);
					//The message to say that we edited a feed
					array_push($messages, __( 'Feed modified', 'lexi' ));
				}
			}
			break;
	}

	//wath we have to show?
	switch($mode) {
		case 'add': //If we are adding a new feed, show the respective form
			break;
		case 'edit': //If we are editing a feed, get the data and show the respective form
			$id=$_GET['id'];
			$table_name = $wpdb->prefix . "lexi";
			$data = $wpdb->get_row("select name, rss, items, showcontent, cached from $table_name where id=$id");
			$name=$data->name;
			$rss=$data->rss;
			$items=$data->items;
			$cached=$data->cached;
			$showcontent=$data->showcontent;
			break;
		case 'up': //If we are moving a feed, get the id and ask to move it
			$id=$_GET['id'];
			if(lexi_up_feed($id)) array_push($messages, __("Feed moved", 'lexi'));
			break;
		case 'down': //If we are moving a feed, get the id and ask to move it
			$id=$_GET['id'];
			if(lexi_down_feed($id)) array_push($messages, __("Feed moved", 'lexi'));
			break;
		case 'delete': //If we are deleting a feed, get the id and ask to delete it
			$id=$_GET['id'];
			if(lexi_delete_feed($id)) array_push($messages, __("Feed deleted", 'lexi'));
			break;
	}

	if($mode=='edit' || $mode=='add') { //if we are editing or adding, we show its respective forms
		include('templates/lexi_feed.php');
	} else { //else show the manage page
		include('templates/lexi_manage.php');
	}
}

/**
* Enable buttons in tinymce.
* This function should be called by an action.
*
* @access public
*/
function lexi_add_buttons() {
	// Don't bother doing this stuff if the current user lacks permissions
	if( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) return;

	// Add only in Rich Editor mode
	if( get_user_option('rich_editing') == 'true') {

		// add the button for wp21 in a new way
		add_filter('mce_external_plugins', 'add_lexi_script');
		add_filter('mce_buttons', 'add_lexi_button');
	}
}

/**
* Function to add the button to the bar.
* This function should be called by a filter.
*
* @access public
*/
function add_lexi_button($buttons) {
	array_push($buttons, 'Lexi');
	return $buttons;
}

/**
* Function to set the script which should answer when the user press the button.
* This function should be called by a filter.
*
* @access public
*/
function add_lexi_script($plugins) {
	$dir_name = '/wp-content/plugins/lexi';
	$url = get_bloginfo('wpurl');
	$pluginURL = $url.$dir_name.'/tinymce/editor_plugin.js';
	$plugins['Lexi'] = $pluginURL;
	return $plugins;
}


/**
* Lexi widget stuff.
*
* @access public
*/
function lexi_widget_init() {

	if( !function_exists('register_sidebar_widget') ) {
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
		
		$options = get_option('widget_lexi');
		$title = $options['title'];
		$show_feed_title = $options['show_feed_title'];
	
		// These lines generate our output. Widgets can be very complex
		// but as you can see here, they can also be very, very simple.
		echo $before_widget;
		if(strlen($title)>0) {
			echo $before_title . $title . $after_title;
		}
		lexi(LEXI_LIST);
		echo $after_widget;
	}
	
	// This is the function that outputs the form to let the users edit
	// the widget's title. It's an optional feature that users cry for.
	function lexi_widget_control() {
	
		// Get our options and see if we're handling a form submission.
		$options = get_option('widget_lexi');
		if( !is_array($options) )
			$options = array('title'=>'', 'show_feed_title'=>1);
			
		if(!function_exists('minimax_version') || minimax_version()<0.3) { ?>
		<p>
			<label>
				<?php printf(__('You have to install <a href="%s" target="_BLANK">minimax 0.3</a> in order for this plugin to work', 'sk'), "http://wordpress.org/extend/plugins/minimax/" ); ?>
			</label>
		</p><?
		} else {
			if( $_POST['lexi-submit'] ) {
				// Remember to sanitize and format use input appropriately.
				$options['title'] = strip_tags(stripslashes($_POST['lexi_title']));
				if($_POST['showfeedtitle']=='on')
					$options['show_feed_title'] = true; 
				else 
					$options['show_feed_title'] = false;
				update_option('widget_lexi', $options); 
			}
			
			// Be sure you format your options to be valid HTML attributes.
			$title = htmlspecialchars($options['title'], ENT_QUOTES);
			$show_feed_title = $options['show_feed_title'];
			
			
			// Here is our little form segment. Notice that we don't need a
			// complete form. This will be embedded into the existing form.
			require('templates/lexi_widget.php');
		}
	}
	
	// This registers our widget so it appears with the other available
	// widgets and can be dragged and dropped into any active sidebars.
	register_sidebar_widget(array('Lexi RSS Widget', 'widgets'), 'lexi_widget');
	
	// This registers our optional widget control form. Because of this
	// our widget will have a button that reveals a 300x100 pixel form.
	register_widget_control(array('Lexi RSS Widget', 'widgets'), 'lexi_widget_control');

}

// Run our code later in case this loads prior to any required plugins.
add_action('widgets_init', 'lexi_widget_init');

?>
