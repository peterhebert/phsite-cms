<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Content editor</title>
<style type="text/css">
<!--
.breadcrumb {
	clear:both;
}
.breadcrumb ul {
	list-style: none;
	margin:0;
}
.breadcrumb ul li {
	float:left;
	margin:0 1ex 0 0;
}
.breadcrumb ul li:after {
	content:"  :  ";
}
-->
</style>
</head>

<body>
<div class="breadcrumb"><?php
$bc = array(
anchor("cms/cms","CMS home"),
anchor("cms/content","Content"),
'Content editor'
);

echo ul($bc);
?></div>
	<h1>Content editor</h1>
<?php
	echo "<p>Content ID: ". $content_id."</p>\n";
	echo "<p>Object ID: ". $object_id."</p>\n";
	echo "<p>Parent ID: ". $parent_id."</p>\n";
	echo "<p>Created: ". $created."</p>\n";
	echo "<p>Updated: ". $updated."</p>\n";
	echo "<code>";
	print_r( $content_data);
	echo "</code>\n";


?>

</body>
</html>
