<?php
/*
Plugin Name: Lexi
Plugin URI: http://www.sebaxtian.com/acerca-de/lexi
Description: An RSS feeder using ajax to show contents after the page has been loaded.
Version: 0.7.96
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

define('CONF_CACHE', 1);
define('CONF_SHOWCONTENT', 2);
define('CONF_SHOWHEADER', 4);
define('CONF_TARGETBLANK', 8);

$db_version=get_option('lexi_db_version');

add_action('init', 'lexi_addbuttons');
add_action('init', 'lexi_textdomain');
add_action('wp_head', 'lexi_header');
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
  * Function to add Lexi's css
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
  * Returns the html code with 'minimax' script and div.
  * Add this code into the html page to create the RSS reader.
  *
  * @param string link The RSS URL
  * @param string title The title to put at the beginning of the list
  * @param string items Max number of feeds to show
  * @param string sc Show feed contents?
  * @param string cache Save feeds in cache?
  * @return string
  * @access public
  */
  
function lexi_postRss($link, $title, $items, $conf) {
  $answer="";
  if(function_exists('minimax') && minimax_version()==0.2) {
    $num = mt_rand();
    $url=lexi_plugin_url('/content.php');
    if($sc) $sc=1; else $sc=0;
    if($cache) $cache=1; else $cache=0;
    $post="url=".urlencode(str_replace("&amp;", "&", $link))."&amp;title=".urlencode(str_replace("&amp;", "&", $title))."&amp;num=$items&amp;conf=$conf";
    $answer.="\n<div id='lexi$num'><table><tr><td><img class='lexi' src='".get_bloginfo('wpurl')."/wp-content/plugins/lexi/img/loading.gif' alt='RSS' border='0' /></td><td>".__('Loading Feed...','lexi')."</td></tr></table></div><script type='text/javascript'>mx_lexi$num = new minimax('$url', 'lexi$num');
    mx_lexi$num.post('$post');
    </script>";
  } else {
    $answer.= "<div id='lexi'><label>";
    $answer.= sprintf(__('You have to install <a href="%s"  target="_BLANK">minimax 0.2</a> in order for this plugin to work', 'lexi'), "http://wordpress.org/extend/plugins/minimax/" );
    $answer.= "</label></div>";
  }
  return $answer;
}


/**
  * Returns the html code with 'minimax' script and div.
  * Add this code into the html page to create the RSS reader.
  *
  * @param int id The feed Id into lexi list.
  * @return string
  * @access public
  */

function lexi_postId($id) {
  global $wpdb;

	$show_feed_title = true;

	if($id==-1) {
		$options = get_option('widget_lexi');
		$show_feed_title = $options['show_feed_title'];
		$id=0;
	}

  $answer="";

  $table_name = $wpdb->prefix . "lexi";
  if($id)
    $feedlist = $wpdb->get_results("SELECT * FROM $table_name WHERE id = $id");
  else
    $feedlist = $wpdb->get_results("SELECT * FROM $table_name ORDER BY position ASC");

  // These lines generate our output. Widgets can be very complex
  // but as you can see here, they can also be very, very simple.
  
  if(function_exists('minimax') && minimax_version()==0.2) {
    foreach($feedlist as $feed) {

			//Configuration
			$conf = CONF_TARGETBLANK;
			if($feed->showcontent) $conf += CONF_SHOWCONTENT;
			if($feed->cached) $conf += CONF_CACHE;
			if($show_feed_title) $conf += CONF_SHOWHEADER;

      if(!$id || $id==$feed->id) {
        $answer.= lexi_postRss($feed->rss,$feed->name,$feed->items,$conf);
      }
    }
  } else {
    $answer.= "<div id='lexi'><label>";
    $answer.= sprintf(__('You have to install <a href="%s"  target="_BLANK">minimax 0.2</a> in order for this plugin to work', 'lexi'), "http://wordpress.org/extend/plugins/minimax/" );
    $answer.= "</label></div>";
  }
  return $answer;
}


/**
  * Filter to manage contents. Check for [lexi] tags.
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
        $replace=lexi_postId($id);
        $content = str_replace ($search, $replace, $content);
      }
    }
  }

  //RSS
  $search = "@(?:<p>)*\s*\[lexi\s*:([^,]+),(\d+),(true|false),(true|false)?\]\s*(?:</p>)*@i";
  if  (preg_match_all($search, $content, $matches))
  {
    if (is_array($matches))
    {
      foreach ($matches[1] as $key =>$rss)
      {
        $items=$matches[2][$key];
        $sc=$matches[3][$key];
        if($sc=='true') $sc=1; else $sc=0;
        $cache=$matches[4][$key];
        if($cache=='true') $cache=1; else $cache=0;

				$conf = CONF_SHOWHEADER + CONF_TARGETBLANK;
				if($sc) $conf += CONF_SHOWCONTENT;
				if($cache) $conf += CONF_CACHE;

        $search = $matches[0][$key];
        $replace=lexi_postRss($rss,"",$items,$conf);
        $content = str_replace ($search, $replace, $content);
      }
    }
  }

	//Lexi 2 w/ title
	$search = "@(?:<p>)*\s*\[lexi\s*:(\d+),([^,]+),([^,]+),(\d+)?\]\s*(?:</p>)*@i";
  if  (preg_match_all($search, $content, $matches))
  {
    if (is_array($matches))
    {
      foreach ($matches[1] as $key =>$conf)
      {
        $rss=$matches[2][$key];
        $title=$matches[3][$key];
        $items=$matches[4][$key];

        $search = $matches[0][$key];
        $replace=lexi_postRss($rss, $title, $items, $conf);
        $content = str_replace ($search, $replace, $content);
      }
    }
  }

	//Lexi 2 w/out title
	$search = "@(?:<p>)*\s*\[lexi\s*:(\d+),([^,]+),(\d+)?\]\s*(?:</p>)*@i";
  if  (preg_match_all($search, $content, $matches))
  {
    if (is_array($matches))
    {
      foreach ($matches[1] as $key =>$conf)
      {
        $rss=$matches[2][$key];        
        $items=$matches[3][$key];

        $search = $matches[0][$key];
        $replace=lexi_postRss($rss, "", $items, $conf);
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
    if(is_numeric($id)) {
      echo lexi_postId($id);
    } else {
      echo lexi_postRss($id, "", $num, $sc, $cached, $sh);
    }
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
  function lexiRSS($conf, $rss, $title, $max_items) {
    if(!$title) $title="";
    echo lexi_postRss($rss, $title, $max_items, $conf); 
  }


/**
  * Returns the HTML list for an RSS feed.
  *
  * @param string link The URL of the rss.
  * @param string name Name to be shown at the top of the list. If empty would use
  * the name in the rss.
  * @param string num Max number of feeds to show.
  * @param string sc Show feed contents?
  * @param string cached Save feeds in cache?
  * @return string
  * @access public
  */
function lexi_readfeed($link, $name, $num, $config) {
  include_once(ABSPATH . WPINC . '/rss.php');
	@include_once(ABSPATH . WPINC . '/class-simplepie.php');
	
	$name=str_replace("\\\"","\"",$name);

	if(($config & CONF_TARGETBLANK)) {
		$target = " target='_blank'";
	}
	
	if(class_exists('SimplePie')) {
		$rss = new SimplePie($link);

		if(!($config & CONF_CACHE)) {
			$rss->enable_cache(false);
			$rss->init();
		}
		
		$channel_link = $rss->get_permalink();
		if($name=="") {
			$name=htmlspecialchars($rss->get_title());
		}
		
		$items = $rss->get_items(0, $num);
		
		if($items) {
			foreach ($items as $item) {
				$answer.="<li><a class='rsswidget' href='".htmlspecialchars($item->get_permalink())."'".$target.">".$item->get_title()."</a>";
				if($config & CONF_SHOWCONTENT) $answer.="<br/>".$item->get_content();
				$answer.="</li>";
			}
		}
		
	} else {
		$aux_cached = MAGPIE_CACHE_ON;
		if(!($config & CONF_CACHE)) {
			define('MAGPIE_CACHE_ON', 0);
		}
		$rss = fetch_rss($link);
		define('MAGPIE_CACHE_ON', $aux_cached);
		$channel_link=htmlspecialchars($rss->channel['link']);
		if($name=="") {
			$name=htmlspecialchars($rss->channel['title']);
		}

		if($rss->items) {
			foreach (array_slice($rss->items, 0, $num) as $item) {
				$answer.="<li><a class='rsswidget' href='".htmlspecialchars($item['link'])."'".$target.">".$item['title']."</a>";
				if($config & CONF_SHOWCONTENT) $answer.="<br/>".$item['atom_content'].$item['summary'];
				$answer.="</li>";
			}
		}
	}
	
  $header="";
	if($config & CONF_SHOWHEADER) {
		$header = "<h2 class='widgettitle'><a class='rsswidget' href='$link' title='" . __('Subscribe' , 'lexi')."'><img class='lexi' src='".get_bloginfo('wpurl')."/wp-includes/images/rss.png' alt='RSS' border='0' /></a> <a class='rsswidget' href='$channel_link' title='$name'>$name</a></h2>";
	}
  return "$header<ul>$answer</ul>";
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

/**
  * Enable buttons in tinymce.
  *
  * @access public
  */
  
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


/**
  * Enable buttons in tinymce.
  *
  * @access public
  */

function add_lexi_button($buttons) {

   array_push($buttons, 'Lexi');
   return $buttons;

}

/**
  * Enable buttons in tinymce.
  *
  * @access public
  */

function add_lexi_script($plugins) {
   $dir_name = '/wp-content/plugins/lexi';
   $url=get_bloginfo('wpurl');
   $pluginURL =  $url.$dir_name.'/tinymce/editor_plugin.js';
   $plugins['Lexi'] = $pluginURL;
   return $plugins;
}


/**
  * Lexi widget stuff.
  *
  * @access public
  */
  
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
		
		$options = get_option('widget_lexi');
		$title = $options['title'];
		$show_feed_title = $options['show_feed_title'];
  
    // These lines generate our output. Widgets can be very complex
    // but as you can see here, they can also be very, very simple.
    echo $before_widget;
		if(strlen($title)>0) {
			echo $before_title . $title . $after_title;
		}
    lexi(-1);
    echo $after_widget;
  }
	
	// This is the function that outputs the form to let the users edit
	// the widget's title. It's an optional feature that users cry for.
	function lexi_widget_control() {
	
		// Get our options and see if we're handling a form submission.
		$options = get_option('widget_lexi');
		if ( !is_array($options) )
			$options = array('title'=>'', 'show_feed_title'=>1);
		if ( $_POST['lexi-submit'] ) {
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
