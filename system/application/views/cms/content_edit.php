<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Content editor</title>
</head>

<body>
	<h1>Content editor</h1>
	<p><?php echo anchor(site_url("cms/content"),"Back to Content list");  ?></p>
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
