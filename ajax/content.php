<?php require_once( '../../../../wp-config.php' ); ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title></title>
	</head>
<body><?php
$nonce = $_POST['nonce'];
$url   = urldecode($_POST['url']);
$nonce_title = 'lexi'.$url;
if(wp_verify_nonce($nonce, $nonce_title)) {
	
	$title = $_POST['title']; 
	$num   = $_POST['num'];
	$conf  = $_POST['conf'];
	$rand  = $_POST['rand'];
	$page  = $_POST['page'];

	echo lexi_read_feed($url, $title, $num, $conf, $rand, $page);
} else {
	_e('Only Lexi can use this link.', 'lexi');
}
?>
</body>
</html>
