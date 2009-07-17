<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php
if(isset($html_header))
{
echo $html_header;
}
?>
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb"><?php echo $breadcrumb; ?></div>
	<div class="page-wrap block">
		<?php
		if(isset($title))
		{
			echo "<h1>{$title}</h1>\r";
		}
		if(isset($content_id))
		{
			echo "<p>content_id: {$content_id}</p>\r";
		}

		if(isset($content_body))
		{
			echo $content_body;
		}
		if(isset($content_keywords))
		{
			echo "<p>Keywords: {$content_keywords}</p>\r";
		}

		?>
	</div>
	<div class="page-wrap block" id="footer"><?php 
	if (isset($copyright)) {
		echo "<p>{$content_copyright}</p>\r";
	} else {
		echo "<p>{$site_meta_copyright}</p>\r";
	}
	echo POWERED_BY;
	?></div>
</div>
</body>
</html>
