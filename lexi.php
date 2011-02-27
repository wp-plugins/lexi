<?php
/*
Plugin Name: Lexi
Plugin URI: http://www.sebaxtian.com/acerca-de/lexi
Description: An RSS feeder using ajax to show contents after the page has been loaded.
Version: 1.1
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

define('LEXI_HEADER_V', 1.2);

define('CONF_CACHE', 1);
define('CONF_SHOWCONTENT', 2);
define('CONF_SHOWHEADER', 4);
define('CONF_TARGETBLANK', 8);
define('CONF_NOTSHOWICON', 16);
define('CONF_SHOWAUTHOR', 32);
define('CONF_SHOWDATE', 64);
define('CONF_PAGINATE', 128);
define('CONF_NOTITEMTITLE', 256);
define('CONF_HREFTITLE', 512);
define('CONF_TRUNCATE', 1024);

define('LEXI_CACHE_TIME', 3600); //One hour

add_action('admin_init', 'lexi_add_buttons');
add_action('init', 'lexi_text_domain', 1);
add_action('wp_head', 'lexi_header');
add_filter('the_content', 'lexi_content');
add_filter('the_excerpt', 'lexi_content');
add_action('wp_ajax_lexi_tinymce', 'lexi_tinymce');
add_action('wp_ajax_lexi_ajax', 'lexi_ajax');
add_action('wp_ajax_nopriv_lexi_ajax', 'lexi_ajax');

/**
* To declare where are the mo files (i18n).
* This function should be called by an action.
*
* @access public
*/
function lexi_text_domain() {
	load_plugin_textdomain('lexi', false, 'lexi/lang');
}

/**
* Function to add the required data to the header in the site.
* This function should be called by an action.
*
* @access public
*/
function lexi_header() {

	//Local URL
	$url = get_bloginfo( 'wpurl' );
	$local_url = parse_url( $url );
	$aux_url   = parse_url(wp_guess_url());
	$url = str_replace($local_url['host'], $aux_url['host'], $url);
	
	//Define custom JavaScript options (jr_qtip)
	$lexi_completion="lexi_completion = function(rand) {}";
	if(function_exists('jr_qtip_for_wordpress')) {
		$jr = get_option('jr_qtip_for_wordpress');
		$lexi_completion="lexi_completion = function(rand) {
		$('div#lexi'+rand+' ul li a[title]').qtip({
			style: {
				name: '".addslashes($jr['tooltip_color'])."', tip: true,
				textAlign: 'center'
			},
			position: { 
				corner: {
					target: '".addslashes($jr['tooltip_target'])."',
					tooltip: '".addslashes($jr['tooltip_position'])."'
				}
			}
		} );
	}";
	}

	// Define custom JavaScript function
	echo "<script type='text/javascript'>
	lexi_i18n_error = '".__("Can\'t read Lexi Feed", 'lexi')."';
	lexi_url = '$url';
	$lexi_completion
	</script>
	";
	
	//Declare javascript
	wp_register_script('lexi', $url.'/wp-content/plugins/lexi/lexi.js', array('sack'), LEXI_HEADER_V);
	wp_enqueue_script('lexi');
	
	//Define custom CSS URI
	$css = get_theme_root()."/".get_template()."/lexi.css";
	if(file_exists($css)) {
		$css_register = get_bloginfo('template_directory')."/lexi.css";
	} else {
		$css_register = lexi_plugin_url("/css/lexi.css");
	}
	//Declare style
	wp_register_style('lexi', $css_register, false, LEXI_HEADER_V);
	wp_enqueue_style('lexi');
	
	// Declare we use JavaScript SACK library for Ajax
	wp_print_scripts( array( 'lexi' ));
	wp_print_styles( array( 'lexi' ));
}

/**
* Function to answer the MCE ajax call.
* This function should be called by an action.
*
* @access public
*/
function lexi_tinymce() {
	// check for rights
    if ( !current_user_can('edit_pages') && !current_user_can('edit_posts') ) 
    	die(__("You are not allowed to be here"));
   	
   	require_once('tinymce/mce_lexi.php');
    
    die();
}

/**
* Function to answer the ajax call.
* This function should be called by an action.
*
* @access public
*/
function lexi_ajax() {
	//Get the data from the post call
	$url   = urldecode($_POST['url']);
	$title = $_POST['title']; 
	$num   = $_POST['num'];
	$conf  = $_POST['conf'];
	$rand  = $_POST['rand'];
	$page  = $_POST['page'];
	
	//Get the div id where we want to show the data
	//$results_id = $_POST['results_div_id'];
	
	//Get the new data.
	$results = lexi_read_feed($url, $title, $num, $conf, $rand, $page); 

	// Compose JavaScript for return
	die( $results );
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
* Returns the html code with 'sack' script and div.
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
	require_once (ABSPATH . WPINC . '/class-feed.php');
	$answer="";
	$num = mt_rand(111111,999999);
	$link = str_replace("&amp;", "&", $link);
	$url=lexi_plugin_url('/ajax/content.php');
	$nonce = wp_create_nonce('lexi'.$link);
	$throbber = "";
	$next_cache_time = 0;
	
	//if we have to use cache, ask for the next cache time
	if($conf & CONF_CACHE) {
		$cache = new WP_Feed_Cache_Transient('./cache', md5($link), 'spc');
		$next_cache_time = $cache->mtime()+LEXI_CACHE_TIME;
	}

	//if we have a cache file and it is not update time, show it
	if(($conf & CONF_CACHE) && $next_cache_time>time()) { //If we use cache and it is new
		$answer.="\n<div id='lexi$num' class='lexi'>".lexi_read_feed($link, $title, $items, $conf, $num, 1)."</div>";
	} else { //Use sack
		// Create the post to ask for the rss feeds
		// Create the div where we want the feed to be shown, and the instance of sack
		$answer.="\n<div id='lexi$num' class='lexi'><table><tr><td><img class='lexi' src='".get_bloginfo('wpurl')."/wp-content/plugins/lexi/img/loading.gif' alt='RSS' border='0' /></td><td>".__('Loading Feed...','lexi')."</td></tr></table></div><script type='text/javascript'>lexi_feed( '$link', '$title', $items, $conf, $num, 1 );</script>";
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
function lexi_calculateConf($use_cache=true, $show_content=false, $show_title=true, $target_blank=true, $icon=true, $show_author=false, $show_date=false, $paginate=false, $show_item_title=true, $href_title=false, $truncate=false ) {	
	//Calculate the conf number
	$config = 0;
	if($use_cache) $config = $config + CONF_CACHE;  //Cache
	if($show_content) $config = $config + CONF_SHOWCONTENT;  //Show contents
	if($show_title)  $config = $config + CONF_SHOWHEADER;  //Show title
	if($target_blank)  $config = $config + CONF_TARGETBLANK;  //Target in new page (blank)
	if(!$icon) $config = $config + CONF_NOTSHOWICON; //Don't show icon
	if($show_author)  $config = $config + CONF_SHOWAUTHOR;  //Show author
	if($show_date)  $config = $config + CONF_SHOWDATE;  //Show date
	if($paginate)  $config = $config + CONF_PAGINATE;  //Show date
	if(!$show_item_title)  $config = $config + CONF_NOTITEMTITLE;  //Not show item title
	if($href_title)  $config = $config + CONF_HREFTITLE;  //Not show item title
	if($truncate)  $config = $config + CONF_TRUNCATE;  //Truncate content
	return $config;
}

/**
* Function to be called in templates. Returns the html code
* with 'sack' script and div. Add this function into the html
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
* with 'sack' script and div. Add this function into the html
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
function lexi_read_feed($link, $name, $num, $config, $rand=false, $group=1) {
	//Use the rss libraries in WP.
	require_once (ABSPATH . WPINC . '/class-feed.php');
	require_once (ABSPATH . WPINC . '/class-simplepie.php');
	$answer = false;
	
	// As this data come from a POST, fix the situation with the dobled quoted strings 
	$name=str_replace("\\\"","\"",$name);

	// Do we have to open links in new pages?
	if(($config & CONF_TARGETBLANK)) {
		$target = " target='_blank'";
	}
	
	//This will change if we need a numbered footer
	$footer="";
		
	// Does simplepie library exists?
	if(class_exists('SimplePie')) {
		//Get the data from the rss
		$rss = new SimplePie();
		
		//Set the feed url
		$rss->set_feed_url($link);

		//Do we have to disable cache?
		if(!($config & CONF_CACHE)) {
			$rss->enable_cache(false);
		} else {
			//Set cache dirname
			$rss->set_cache_class('WP_Feed_Cache');
			$rss->set_file_class('WP_SimplePie_File');
			$rss->set_cache_duration(LEXI_CACHE_TIME); 
		}
		
		//Get the feed
		$rss->init();
		
		//Ǵet the link to the page (not to the RSS) from the feed
		$channel_link = $rss->get_permalink();
		
		//If we don't have a title, use the name from the feed
		if($name=="") {
			$name=htmlspecialchars($rss->get_title());
		}
		
		//Get the items to show
		$start = ($group-1)*$num;
		$items = $rss->get_items($start, $num);
		
		//If we need a footer
		if($config & CONF_PAGINATE) {
			$footer = "<div>".lexi_page_selector($rss, $link, $name, $num, $config, $rand, $group)."</div>";
		}
		
		//If we have something to show, show the items
		if($items) {
			foreach($items as $item) {
				//Every feed is an item in the list.
				//In this link we use:
				//		link to the feed
				//		target to define if we need to open the link in a ned window
				//		title of the feed
				//		the content and the variable to know if we have to show it
				$aux="";
				$content = $item->get_content();
				if($config & CONF_TRUNCATE) $content = lexi_truncate($content, $truncate);
				if(!($config & CONF_NOTITEMTITLE)) {
					$item_link = $item->get_link();
					while ( stristr($item_link, 'http') != $item_link )
						$item_link = substr($item_link, 1);
					$item_link = esc_url(strip_tags($item_link));
					$title = esc_attr(strip_tags($item->get_title()));
					if ( empty($title) )
					$title = __('Untitled');
					$aux="<a class='rsswidget' href='$item_link' ";
					if($config & CONF_HREFTITLE) $aux.="title='". htmlspecialchars(html_entity_decode($content), ENT_QUOTES)."' ";
					$aux.="$target>$title</a>";
				}
				
				if($config & CONF_SHOWDATE) {
					$date = $item->get_date();
					if ( $date ) {
						if ( $date_stamp = strtotime( $date ) )
							$date = ' <span class="rss-date">' . date_i18n( get_option( 'date_format' ), $date_stamp ) . '</span>';
						else
							$date = '';
					}
					$aux.="<br/>".$date;
				}
				
				if($config & CONF_SHOWCONTENT) $aux.="<br/>".$content;
				
				if($config & CONF_SHOWAUTHOR) {
					$author = $item->get_author();
					if ( is_object($author) ) {
						$author = $author->get_name();
						$author = ' <cite>(' . esc_html( strip_tags( $author ) ) . ')</cite>';
					}
					$aux.=$author;	
				}
				
				if($num>1) {
					$aux="<li>$aux</li>";
				}
				$answer.=$aux;
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
	if($num==1) {
		$answer = "$header $answer ";
	} else {
		$answer = "$header<ul>$answer</ul>";
	}
	return apply_filters('comment_text', $answer).$footer;
}

/**
* Returns HTML for 'page selector' footer
*
* @param rss The RSS object from simplepie
* @param int group Which group are we showing?
* @param int size Items per group
* @param rand The group identifier (random)
* @return string
* @access public
*/
function lexi_page_selector($rss, $link, $name, $num, $config, $rand=false, $group=1) {
	global $wpdb;
	
	if(!$rand) $rand = mt_rand(111111,999999);
	
	$uri_lexi=lexi_plugin_url('/content.php?page');
	$answer="";
	$total_groups=5; //We will show only 3 groups
	$style_actual_group="";
	$style_no_actual_group="";
	$first_item= "&#171;";
	$last_item= "&#187;";
	
	//Create nonce
	$nonce = wp_create_nonce('lexi'.$link);
	
	// Get the number of comments we have
	$total = count($rss->get_items());
	
	//Get the number of groups we have
	$size = $num;
	$groups=ceil($total/$size);
	
	//By default we start with the first group and end with the number of groups
	//With this we define the interval to show
	$group_start=1;
	$group_end=$total_groups;
	
	//A number to determine thye groups to show
	$group_limit=ceil($total_groups/2)-1;

	//If the number of groups is lesser or equar than the number of groups to show
	if($groups<=$total_groups) {
		$group_end=$groups; //The start group is 1,and the end group is the number of groups
	} else {
		if($groups-$group<=$group_limit) {	// If the difference between the total groups 
														// to show and the group we are showing is 
														// lesser or equal to the group limit.
														// It means we are so close to the end so we have to 
														// show the total number of groups at the end and
														// calculate the begin 
			$group_start=$groups-$total_groups+1; //The start group is the groups minus the total groups to show pluss 1 
			$group_end=$groups; // The end group is the number of groups
		} else {
			if($group>$group_limit) { 	// If the group to show is greater than the group limit. 
												// It means we are far away from the begin so we can 
												// show calculate the list and set the group in the middle.
				$group_start=$group-$group_limit; //The start is the group to show minus the group limit
				$group_end=$group+$group_limit; //The end is the group to show plus the group limit
			}
		}
	}

	//If the list doesn't start from 1, create a link to go to the benginig
	$post="nonce=$nonce&amp;url=".urlencode(str_replace("&amp;", "&", $link))."&amp;title=$name&amp;num=$num&amp;conf=$config&amp;rand=$rand&amp";
	if($group_start!=1) {
		$answer.="<a class='lexi-page-other' onclick=\"
				var aux = document.getElementById('lexi-page$rand');
				aux.setAttribute('class', 'lexi-page-on');
				aux.setAttribute('className', 'lexi-page-on'); //IE sucks
				lexi_feed( '$link', '$name', $num, $config, $rand, 1 );
				document.getElementById('lexi$rand').value=1;
				\">$first_item</a> &#183; ";
	}
	
	//Create the page list and the links
	for($group_id=$group_start; $group_id<=$group_end; $group_id++) {
		$style=$style_no_actual_group;
		if($group_id==$group) {
			$answer.="<span class='lexi-page-actual'>$group_id</span> &#183; ";
		} else {
			$answer.="<a class='lexi-page-other' onclick=\"
				var aux = document.getElementById('lexi-page$rand');
				aux.setAttribute('class', 'lexi-page-on');
				aux.setAttribute('className', 'lexi-page-on'); //IE sucks
				lexi_feed( '$link', '$name', $num, $config, $rand, $group_id );
				document.getElementById('lexi$rand').value=$group_id;
				\">$group_id</a> &#183; ";
		}
	}

	//If the list doesn't finish with the last group, create a link to the end
	if($group_end!=$groups) {
	$answer.="<a class='lexi-page-other'
			onclick=\"
			var aux = document.getElementById('lexi-page$rand');
			aux.setAttribute('class', 'lexi-page-on');
			aux.setAttribute('className', 'lexi-page-on'); //IE sucks
			lexi_feed( '$link', '$name', $num, $config, $rand, $groups );
			document.getElementById('lexi$rand').value=$groups;
			\">$last_item</a> &#183; ";
	}

	//As every link ends with a line, delete the last one as we don't need it
	$answer = substr($answer,0,-8);
	return "<br/><div id='lexi-page$rand' class='lexi-page-off'><small>$answer</small></div>";
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
	$pluginURL = $url.$dir_name.'/tinymce/editor_plugin.js?ver='.LEXI_HEADER_V;
	$plugins['Lexi'] = $pluginURL;
	return $plugins;
}

/**
* Function to truncate any html text using.
*
* @param text Text to truncate.
* @access public
*/
function lexi_truncate ($text) {

	if(defined('LEXI_TRUNCATE_SIZE')) {
		$truncate_size=LEXI_TRUNCATE_SIZE;
	} else {
		$truncate_size=false;
	}

	if(!$truncate_size) $size = 400; else $size = $truncate_size;

	//Change any <br> with new line
	$br_regex = "/<[br\s\/]{2,}>/i" ;
	$text = preg_replace ($br_regex, "\n", $text);
	//Change any </n> with 2 new line
	$p_regex = "/<\/p>/i" ;
	$text = preg_replace ($p_regex, "\n\n", $text);
	//Delete any html tag
	$html_regex = "/<(img|div|\/div|table|\/table|td|\/td|tr|\/tr|p|\/p|b|\/b|a|\/a|pre|\/pre)[^\>]*>/i" ;
	$text = preg_replace ($html_regex, "", $text);
	//Join new lines
	$html_regex = "/\n\s*\n/i";
	$text = preg_replace ($html_regex, "\n\n", $text);
	//Erase extra new lines
	$html_regex = "/\n(\n)+/i";
	//Erase first new line
	$text = preg_replace ($html_regex, "\n\n", $text);
	
	//Internal code
	//$html_regex = "/\n/i";
	//$text = preg_replace ($html_regex, "[ltnl]", $text);
	
	$text = trim($text);
	if($truncate_size) { //As the user have defined a truncate size, use it
		$text = cake_truncate($text, $size, ' ...', false, true);
		$text = trim($text);
	} else { //We have to get the first paragraphs with enough text.
		//Explode in paragraphs
		$pieces = explode("\n\n", $text);
		
		//Concatenate until we have something to show
		$text = "";
		foreach($pieces as $piece) {
			if(strlen($text)+strlen($piece)<$size || strlen($text)==0)
				$text.= "\n\n$piece"; 
		}
		$text = trim($text);
	}
	
	return $text;
}

/**
* Truncates text.
*
* Cuts a string to the length of $length and replaces the last characters
* with the ending if the text is longer than length.
*
* @param string  $text String to truncate.
* @param integer $length Length of returned string, including ellipsis.
* @param mixed $ending If string, will be used as Ending and appended to the trimmed string. Can also be an associative array that can contain the last three params of this method.
* @param boolean $exact If false, $text will not be cut mid-word
* @param boolean $considerHtml If true, HTML tags would be handled correctly
* @return string Trimmed string.
*/
function cake_truncate($text, $length = 100, $ending = '...', $exact = true, $considerHtml = false) {
    if (is_array($ending)) {
        extract($ending);
    }
    if ($considerHtml) {
        if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
            return $text;
        }
        $totalLength = mb_strlen($ending);
        $openTags = array();
        $truncate = '';
        preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);
        foreach ($tags as $tag) {
            if (!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2])) {
                if (preg_match('/<[\w]+[^>]*>/s', $tag[0])) {
                    array_unshift($openTags, $tag[2]);
                } else if (preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag)) {
                    $pos = array_search($closeTag[1], $openTags);
                    if ($pos !== false) {
                        array_splice($openTags, $pos, 1);
                    }
                }
            }
            $truncate .= $tag[1];
 
            $contentLength = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $tag[3]));
            if ($contentLength + $totalLength > $length) {
                $left = $length - $totalLength;
                $entitiesLength = 0;
                if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE)) {
                    foreach ($entities[0] as $entity) {
                        if ($entity[1] + 1 - $entitiesLength <= $left) {
                            $left--;
                            $entitiesLength += mb_strlen($entity[0]);
                        } else {
                            break;
                        }
                    }
                }
 
                $truncate .= mb_substr($tag[3], 0 , $left + $entitiesLength);
                break;
            } else {
                $truncate .= $tag[3];
                $totalLength += $contentLength;
            }
            if ($totalLength >= $length) {
                break;
            }
        }
 
    } else {
        if (mb_strlen($text) <= $length) {
            return $text;
        } else {
            $truncate = mb_substr($text, 0, $length - strlen($ending));
        }
    }
    if (!$exact) {
        $spacepos = mb_strrpos($truncate, ' ');
        if (isset($spacepos)) {
            if ($considerHtml) {
                $bits = mb_substr($truncate, $spacepos);
                preg_match_all('/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER);
                if (!empty($droppedTags)) {
                    foreach ($droppedTags as $closingTag) {
                        if (!in_array($closingTag[1], $openTags)) {
                            array_unshift($openTags, $closingTag[1]);
                        }
                    }
                }
            }
            $truncate = mb_substr($truncate, 0, $spacepos);
        }
    }
 
    $truncate .= $ending;
 
    if ($considerHtml) {
        foreach ($openTags as $tag) {
            $truncate .= '';
        }
    }
 
    return $truncate;
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
			
			$show_content = 0;
			$show_href_title = 0;
			$truncate = 0;
			
			if(!isset($instance['content'])) {
				if($instance['show_content'] == '0' && $instance['show_href_title'] == '0') $instance['content'] = 0;
				if($instance['show_content'] == '1') $instance['content'] = 1;
				if($instance['show_content'] == '0' && $instance['show_href_title'] == '1') $instance['content'] = 3;
			}
			
			switch($instance['content']) {
				case '1':
					$show_content = 1;
					$show_href_title = 0;
					$truncate = 0;
					break;
				case '2':
					$show_content = 1;
					$show_href_title = 0;
					$truncate = 1;
					break;
				case '3':
					$show_content = 0;
					$show_href_title = 1;
					$truncate = 1;
					break;
			}
			
			$config = lexi_calculateConf($instance['use_cache'], $show_content, $instance['show_title'], $instance['target_blank'], $instance['icon'], $instance['show_author'], $instance['show_date'], $instance['paginate'], !$instance['not_show_item_title'], $show_href_title, $truncate );
			
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
			$instance['content'] = $new_instance['content'];
			if($new_instance['show_title']) $instance['show_title'] = 1; else $instance['show_title'] = 0;
			if($new_instance['icon']) $instance['icon'] = 1; else $instance['icon'] = 0;
			if($new_instance['target_blank']) $instance['target_blank'] = 1; else $instance['target_blank'] = 0;
			if($new_instance['show_author']) $instance['show_author'] = 1; else $instance['show_author'] = 0;
			if($new_instance['show_date']) $instance['show_date'] = 1; else $instance['show_date'] = 0;
			if($new_instance['paginate']) $instance['paginate'] = 1; else $instance['paginate'] = 0;
			if($new_instance['not_show_item_title']) $instance['not_show_item_title'] = 0; else $instance['not_show_item_title'] = 1; //As we ask to 'show the title' (more easy to understand), we have to 'false' it.
			if($new_instance['show_href_title']) $instance['show_href_title'] = 1; else $instance['show_href_title'] = 0;
			
			return $instance;
		}
		
		/**
		 *	admin control form
		 */	 	
		function form($instance) {
			$default = 	array('rss'=> '', 'title'=>'', 'items'=>'5', 'show_title'=>'1', 'icon'=>'1', 'not_show_item_title'=>'0', 'target_blank'=>'1', 'use_cache'=>'1', 'show_author'=>'0', 'show_date'=>'0', 'paginate'=>'0', 'content'=>'0');
			$instance = wp_parse_args( (array) $instance, $default );
			
			if(isset($instance['show_content'])) {
				if($instance['show_content'] == '0' && $instance['show_href_title'] == '0') $instance['content'] = 0;
				if($instance['show_content'] == '1') $instance['content'] = 1;
				if($instance['show_content'] == '0' && $instance['show_href_title'] == '1') $instance['content'] = 3;
				unset($instance['show_content']);
				unset($instance['show_href_title']);
			}
			
			if(!array_key_exists('not_show_item_title',$instance)) { //Maybe we are showing a widget whitout 'not_show_item_title' variable,we have to create it as default
				$instance['not_show_item_title']=0;
			}
			
			//Show the widget control.
			include('templates/lexi_widget_manage.php');
		}
	}

	/* register widget when loading the WP core */
	add_action('widgets_init', 'lexi_register_widgets');

	function lexi_register_widgets() {
		register_widget('LexiWidget');
	}

}

?>
