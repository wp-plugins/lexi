<?php
/*
Plugin Name: Lexi
Plugin URI: http://www.sebaxtian.com/acerca-de/lexi
Description: An RSS feeder using ajax to show contents after the page has been loaded.
Version: 0.9
Author: Juan Sebastián Echeverry
Author URI: http://www.sebaxtian.com
*/

/* Copyright 2007-2010 Juan Sebastián Echeverry (email : sebaxtian@gawab.com)

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

require_once("legacy.php");

define('CONF_CACHE', 1);
define('CONF_SHOWCONTENT', 2);
define('CONF_SHOWHEADER', 4);
define('CONF_TARGETBLANK', 8);
define('CONF_NOTSHOWICON', 16);

add_action('init', 'lexi_add_buttons');
add_action('init', 'lexi_text_domain', 1);
add_action('wp_head', 'lexi_header');
add_filter('the_content', 'lexi_content');
add_action('activate_plugin', 'lexi_activate');

//The activation error routine
if ($_GET['action'] == 'error_scrape') {
	//Activate the i18n
	lexi_text_domain();
	
	//Get the cache directory path
	$ans = parse_url(get_bloginfo('wpurl'));
	$cache_dir = $_SERVER['DOCUMENT_ROOT'].$ans['path'].'/wp-content/cache';
	
	//Error 1: we can't create the cache directory
	if(!file_exists($cache_dir.'/lexi')) {
		die(sprintf(__("Can't create the cache dir: %s<br>You have to create it manually.", 'lexi'), $cache_dir.'/lexi/'));
	}
	
	//Error 2: we can't write in the cache directory
	if($handle = @fopen($cache_dir.'/lexi/test.txt','w')) {
		fclose($handle);
		unlink($cache_dir.'/lexi/test.txt');
	} else {
		die(sprintf(__("Can't write in the cache dir: %s<br>You have to modify the permissions manually.", 'lexi'), $cache_dir.'/lexi/'));
	}
}

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
		
	//Create the cache directory if it doesn't exist
	$ans = parse_url(get_bloginfo('wpurl'));
	$cache_dir = $_SERVER['DOCUMENT_ROOT'].$ans['path'].'/wp-content/cache';
	
	if(!file_exists($cache_dir)) mkdir($cache_dir);
	if(!file_exists($cache_dir.'/lexi')) mkdir($cache_dir.'/lexi');
	
	//Error 1: we can't find or create the cache directory.
	if(!file_exists($cache_dir.'/lexi')) {
		trigger_error('Lexi-error: #1', E_USER_ERROR);
	}
	
	//Error 2: we can't write in the cache directory
	if($handle = fopen($cache_dir.'/lexi/test.txt','w')) {
		fclose($handle);
		unlink($cache_dir.'/lexi/test.txt');
	} else {
		trigger_error('Lexi-error: #2', E_USER_ERROR);
	}
	
	//Have we updated or no?
	//Is this widget a multiwidget?
	$options = get_option('widget_lexi');
	if ( !array_key_exists('_multiwidget', $options) ) {
		// old format, conver if single widget
		$settings = wp_convert_widget_settings('lexi', 'widget_lexi', $options);
		//Update the widget into multiple widgets, create the 'legacy' descriptor
		lexiLegacy_updateWidget();
	}

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
* Filter to manage contents. Check for [lexi] tags.
* This function should be called by a filter.
*
* @access public
* @param string content The content to change.
* @return The content with the changes the plugin have to do.
*/
function lexi_content($content) {

	//The legacy content
	$content = lexiLegacy_content($content);
	
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
				$conf =  lexi_calculateConf($cache, $sc);
				
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
* Function to calculate the conf number
* @param int use_cache Use the cache system, default true
* @param int show_content Show content, default false
* @param int show_title Show title, default true
* @param int target_blank Opemn feed in a new page, default true
* @param int icon Show RSS icon, default true
* @acces public
* @return int The conf number
*/
function lexi_calculateConf($use_cache=true, $show_content=false, $show_title=true, $target_blank=true, $icon=true ) {	
	//Calculate the conf number
	$config = 0;
	if($use_cache) $config = $config + CONF_CACHE;  //Cache
	if($show_content) $config = $config + CONF_SHOWCONTENT;  //Show contents
	if($show_title)  $config = $config + CONF_SHOWHEADER;  //Show title
	if($target_blank)  $config = $config + CONF_TARGETBLANK;  //Target in new page (blank)
	if(!$icon) $config = $config + CONF_NOTSHOWICON; //Don't show icon
	return $config;
}

/**
* Function to be called in templates. Returns the html code
* with 'minimax' script and div. Add this function into the html
* page to create the RSS reader.
*
* @param string rss The URL from the feed.
* @param string num Max number of feeds to show
* @param string sc Show feed contents?
* @param string cached Save feeds in cache?
* @access public
*/
function lexi($rss, $num=0, $sc=false, $cached=true, $sh=true) {
	if(!is_numeric($rss)) {
		$conf = lexi_calculateConf($cached, $sc, $sh );
		echo lexi_viewer_rss($rss, "", $num, $conf); 
	} else { //From the old API... call the legacy function
		echo lexiLegacy_lexi($rss);
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
*		add 16 to not show the icon.
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
*		add 16 to not show the icon.
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
* Lexi widget stuff (New MultiWidget )
*
*/
	
// check version. only 2.8 WP support class multi widget system
global $wp_version;
if((float)$wp_version >= 2.8) { //The new widget system
	
	class LexiWidget extends WP_Widget {
	
	/**
		 * constructor
		 */	 
		function LexiWidget() {
			$control_ops = array( 'width' => 420, 'height' => 280 );
			parent::WP_Widget('lexi', 'Lexi', array('description' => __('Add an RSS feed to the sidebar using Lexi.', 'lexi') ), $control_ops);
			
		}
		
		/**
		 * display widget
		 */	 
		function widget($args, $instance) {
			extract($args, EXTR_SKIP);
			
			$config = lexi_calculateConf($instance['use_cache'], $instance['show_content'], $instance['show_title'], $instance['target_blank'], $instance['icon'] );
			
			$rss = $instance['rss'];
			$title = $instance['title'];
			$max_items = $instance['items'];
			
			/*$icon = "";
			if($instance['icon']) $icon = "<a class='rsswidget' href='$rss' title='" . __('Subscribe' , 'lexi')."'><img class='lexi' src='".get_bloginfo('wpurl')."/wp-includes/images/rss.png' alt='RSS' border='0' /></a> ";
			$title = empty($instance['title']) ? '&nbsp;' : apply_filters('widget_title', $instance['title']);*/
			
			echo $before_widget;
			//if ( !empty( $title ) && !$instance['show_title'] ) { echo $before_title . $icon . $title . $after_title; };
			echo lexiRss($config, $rss, $title, $max_items);
			echo $after_widget;
			
		}
		
		/**
		 *	update/save function
		 */	 	
		function update($new_instance, $old_instance) {
			
			$instance = $new_instance;
			
			if($new_instance['use_cache']) $instance['use_cache'] = 1; else $instance['use_cache'] = 0;
			if($new_instance['show_content']) $instance['show_content'] = 1; else $instance['show_content'] = 0;
			if($new_instance['show_title']) $instance['show_title'] = 1; else $instance['show_title'] = 0;
			if($new_instance['icon']) $instance['icon'] = 1; else $instance['icon'] = 0;
			if($new_instance['target_blank']) $instance['target_blank'] = 1; else $instance['target_blank'] = 0;
			
			return $instance;
		}
		
		/**
		 *	admin control form
		 */	 	
		function form($instance) {
			$default = 	array('rss'=> '', 'title'=>'', 'items'=>'5', 'show_content'=>'0', 'show_title'=>'1', 'icon'=>'1', 'target_blank'=>'1', 'use_cache'=>'1');
			$instance = wp_parse_args( (array) $instance, $default );
			
			//Show the widget control.
			include('templates/lexi_widget_manage.php');
		}
	}

	/* register widget when loading the WP core */
	add_action('widgets_init', lexi_register_widgets);

	function lexi_register_widgets() {
		register_widget('LexiWidget');
	}

}

?>
