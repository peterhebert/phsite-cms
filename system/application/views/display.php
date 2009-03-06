<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?php echo $title; ?></title>
</head>

<body>
	<?php
	if(isset($msg)) {
		echo "<p><strong>$msg</strong></p>\n";
	}
	?>

	<h1><?php echo $title; ?></h1>
	<p><?php echo $breadcrumb; ?></p>
	<p>Created: <?php echo $created; ?></p>
	<p>Updated: <?php echo $updated; ?></p>

	<pre><?php print_r($content_data);  ?></pre>

</body>
</html>
