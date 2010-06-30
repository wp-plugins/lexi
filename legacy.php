<?php
/*
These are the functions to mantain compatibility with legacy versions prior to Lexi 0.9
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

/**
* Function to update lexi into the new widget.
*/
function lexiLegacy_updateWidget() {
	global $wpdb;
	$table_name = $wpdb->prefix . "lexi";
	$options = get_option('widget_lexi');
	
	//Start the XML file for legacy feeds.
	$xml="<?xml version = '1.0' encoding = 'UTF-8'?><lexilegacy>";
	
	// Get the feeds
	$feedlist = $wpdb->get_results("SELECT * FROM $table_name ORDER BY position ASC");
	$count=2;
	if(count($feedlist)>0) {
		foreach($feedlist as $feed) {
			$feed_id = $feed->id;
			$rss = str_replace ( array ( '&', '"', "'", '<', '>' ), array ( '&amp;' , '&quot;', '&#39;' , '&lt;' , '&gt;' ), $feed->rss);
			$title = str_replace ( array ( '&', '"', "'", '<', '>' ), array ( '&amp;' , '&quot;', '&#39;' , '&lt;' , '&gt;' ), $feed->name);
			$items = $feed->items;
			if($feed->showcontent) $sc = '1'; else $sc = '0';
			if($feed->cached) $uc = '1'; else $uc = '0';
			
			//Calculate the conf number
			$conf = lexi_calculateConf($uc, $sc);
			
			// Create an array for each feed
			$data = 	array('rss'=> $rss, 'title'=>$title, 'items'=>$items, 'show_content'=>$sc, 'show_title'=>'1', 'icon'=>'1', 'target_blank'=>'1', 'use_cache'=>$uc);
			$xml.="<feed id='$feed_id'><rss>$rss</rss><title>$title</title><items>$items</items><conf>$conf</conf></feed>"; //Add the feed to the XML file
			$options[$count] = $data;
			$count++;
		}
	}
	
	//End the XML file for legacy feeds.
	$xml.="</lexilegacy>";
	
	$add = $count-2;
	$count = 0;
	$changed = false;
	
	if($add>0) { //Do we have something?
		//Create the legacy file descriptor
		$ans = parse_url(get_bloginfo('wpurl'));
		$cache_dir = $_SERVER['DOCUMENT_ROOT'].$ans['path'].'/wp-content/cache/lexi/';
		if( $fwr = @fopen($cache_dir."legacy.xml", "w")) {
			fwrite($fwr,$xml);
			fclose($fwr);
		}
		
		//Delete the old database
		$table_name = $wpdb->prefix . "lexi";
		if($wpdb->get_var("show tables like '$table_name'") == $table_name) {

			$sql = "DROP TABLE $table_name;";

			$wpdb->query($sql);
			delete_option('lexi_db_version');
		}
		
		//Update the widget data
		update_option('widget_lexi', $options);
		//Update the widgets in the sidebar
		$pos = 0; //The position where the widget is		
		$sidebars_widgets = get_option('sidebars_widgets');
		foreach ( (array) $sidebars_widgets as $index => $sidebar ) {
			if ( is_array($sidebar) ) {
				$count = 0; //New counter
				foreach ( $sidebar as $i => $name ) {
					//Check if the widget has the name from the old one
					if ( $name == 'lexi-rss-widget') {
						//We found something, set the data
						$pos = $count;
						$changed = true;
					}
					$count++;
				}
				//If we found the widget, move all the widgets after the one where we are 
				//searching, then add the new widgets
				if($changed) {
					//How many widgets do we have in this sidebar?
					$size = count($sidebar);
					
					//Add from end to begin
					$aux=0;
					$sidebar_aux = array();
					for($i=0; $i<$size; $i++) {
						if($i == $pos) {
							for($j=2;$j<$add+2;$j++) {
								$sidebar_aux[$count] = "lexi-$j"; 
								$count++;
							}
						} else {
							$sidebar_aux[$count] = $sidebar[$i];
							$count++;
						}
					}
					
					//Update the sidebars_widgets
					$sidebars_widgets[$index]=$sidebar_aux;
					update_option('sidebars_widgets', $sidebars_widgets);
					$changed = false;
				}
			}
		}
	}
}

function lexiLegacy_content($content) {
	//Show a feed from the internal list
	$search = "@(?:<p>)*\s*\[lexi\s*(:\s*\d+)?\]\s*(?:</p>)*@i";
	if(preg_match_all($search, $content, $matches)) {
		if(is_array($matches)) {
			foreach($matches[1] as $key =>$v0) {
				// Get the data from the tag
				$v1=$matches[1][$key];
				$id=-1; //Show all feeds
				if($v1) {
					$v1=substr($v1,1);
					$id=$v1*1;
				}
				
				$search = $matches[0][$key];
				// Create the scripts to show the feed
				$replace = lexiLegacy_lexi($id);
				$content = str_replace ($search, $replace, $content);
			}
		}
	}
	return $content;
}

/**
* Function to read the legacy file and call the feed.
*/
function lexiLegacy_lexi($id) {
	global $post, $current_user;
	//Legacy show all list is -1
	$answer = ""; 
	$ans = parse_url(get_bloginfo('wpurl'));
	$url = $_SERVER['DOCUMENT_ROOT'].$ans['path'].'/wp-content/cache/lexi/legacy.xml';
	if($data = lexi_readfile($url)) { //We have a legacy file
		$data = new SimpleXMLElement($data);
		foreach($data->feed as $feed) {
			$attr = $feed->attributes();
			if($id == -1 || $id == $attr->id) { //Show the entyre list or this is what we have to show?
				$answer.= lexi_viewer_rss($feed->rss, $feed->title, $feed->items, $feed->conf);
			}
		}
	}
	
	//Section to ask to use the new API
	if($post->post_author == $current_user->id || current_user_can('edit_others_posts')) { //Can this user edit the post
		$answer.= "<div style='color: #FF0000; border: 1px #000 solid; padding: 10px; margin: 10px;'>".sprintf(__('This message should be viewed only by the post owner or editor.<br>The tag or function you are using here has been deprecated.<br>See the <a href=\'%s\'>readme</a> file to change into the new scheme.', 'lexi'), 'http://wordpress.org/extend/plugins/lexi/')."</div>";
	}
	
	return $answer;
}

/**
* A kind of readfile function to determine if use Curl or fopen.
*
* @access public
* @param string filename URI of the File to open
* @return The content of the file
*/
function lexi_readfile($filename)
{
	//Just to declare the variables
	$data = false;
	$have_curl = false;
	$local_file = false;
	
	if(function_exists(curl_init)) { //do we have curl installed?
		$have_curl = true;
	}
	
	$search = "@([\w]*)://@i"; //is the file to read a local file?
	if (!preg_match_all($search, $filename, $matches)) {
		$local_file = true;
	}
	
	if($local_file) { //A local file can be handle by fopen
		if($fop = @fopen($filename, 'r')) {
			$data = null;
			while(!feof($fop))
				$data .= fread($fop, 1024);
			fclose($fop);
		}
	} else { //Oops, an external file
		if($have_curl) { //Try with curl
			if($ch = curl_init($filename)) {
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				$data=curl_exec($ch);
				curl_close($ch);
			}
		} else { //Try with fsockopen
			$url = parse_url($filename);
			if($fp = fsockopen($url['host'], 80)) {
				//Enviar datos POST
				fputs($fp, "POST " . $url['path'] . " HTTP/1.0\r\n");
				fputs($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
				fputs($fp, "Content-Length: " . strlen($url['query']) . "\r\n");
				fputs($fp, "Connection: close \r\r\n\n");
				fputs($fp, $url['query'] . "\r\n");
				 
				//Obtener datos
				while(!feof($fp))
				    $data .= fgets($fp, 1024);
				fclose($fp);
				
				$chunked = false;
				$http_status = trim(substr($data, 0, strpos($data, "\n")));
				if ( $http_status != 'HTTP/1.1 200 OK' ) {
					die('The web service endpoint returned a "' . $http_status . '" response');
				}
				if ( strpos($data, 'Transfer-Encoding: chunked') !== false ) {
					$temp = trim(strstr($data, "\r\n\r\n"));
					$data = '';
					$length = trim(substr($temp, 0, strpos($temp, "\r")));
					while ( trim($temp) != "0" && ($length = trim(substr($temp, 0, strpos($temp, "\r")))) != "0" ) {
						$data .= trim(substr($temp, strlen($length)+2, hexdec($length)));
						$temp = trim(substr($temp, strlen($length) + 2 + hexdec($length)));
					}
				} elseif ( strpos($data, 'HTTP/1.1 200 OK') !== false ) {
					$data = trim(strstr($data, "\r\n\r\n"));
				}
			}
		}
	}

	return $data;
}

?>
