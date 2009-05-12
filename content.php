<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title></title>
	</head>
<body><?php
require_once( '../../../wp-config.php' );

$url   = $_POST['url'];

$title = $_POST['title']; 

$num   = $_POST['num'];

$sc    = 0;
if($_POST['sc']) $sc = 1;

$cache = 0;
if($_POST['cache']) $cache = 1;

$sh    = 1;

$config = $cache*CONF_CACHE + $sc*CONF_SHOWCONTENT + $sh*CONF_SHOWHEADER;

echo lexi_readfeed($url, $title, $num, $config);

?>
</body>
</html>
