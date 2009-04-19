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
$sc    = $_POST['sc'];
$cache = $_POST['cache'];

echo lexi_readfeed($url, $title, $num, $sc, $cache);

?>
</body>
</html>
