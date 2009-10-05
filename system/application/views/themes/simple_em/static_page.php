<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php
if(isset($html_header))
{
echo $html_header;
}
?>
<script type="text/javascript" src="/assets/js/jquery-1.3.1.min.js"></script>
<script type="text/javascript" src="/assets/js/jquery_external_links.js"></script>
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
<?php
if(isset($breadcrumb_links))
{
	$breadcrumb = implode(' : ', $breadcrumb_links);
	echo "\t<div class=\"page-wrap block\" id=\"breadcrumb\">{$breadcrumb}</div>\r";
}
?>

	
	<div class="page-wrap block five">
		<div class="column w3">
			<?php
			if(isset($title))
			{
				echo "<h1>{$title}</h1>\r";
			}
			if(isset($subtitle))
			{
				echo "<h2>{$subtitle}</h2>\r";
			}
			if(isset($content_body))
			{
				echo $content_body;
			}
			if(isset($content_keyword_links))
			{
				echo "<p>Keywords: {$content_keyword_links}</p>\r";
			}
			?>
		</div>
		<div class="float w2">
			<?php
			if(isset($menu_title))
			{
				echo "<h3>{$menu_title}</h3>\r";
			}
			if(isset($menu))
			{
				echo $menu;
			}
			?>
		</div>
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
