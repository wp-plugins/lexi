<?php require_once( '../../../wp-config.php' ); ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title></title>
	</head>
<body><?php

$url   = $_POST['url'];
$title = $_POST['title']; 
$num   = $_POST['num'];
$conf  = $_POST['conf'];

echo lexi_read_feed($url, $title, $num, $conf);

?>
</body>
</html>
