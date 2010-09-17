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
<!-- start page wrapper -->
<div class="wrap pad-h">
	<div id="container" class="wide margins">
		<div id="content" class="column"><?php

		if(isset($content['title'])) 
		{
			echo "<h1>{$content['title']}</h1>\r";
		}
		if(isset($content['subtitle']) && $content['subtitle'] != '') 
		{
			echo "<h2>{$content['subtitle']}</h2>\r";
		}
		if(isset($content['author'])) 
		{
			$user_name_formatted = $content['author']['name_first'].' '.$content['author']['name_last'];
			$byline = sprintf($cms_labels['posted_by'],$content['date_created'],$user_name_formatted);
			echo "<p>{$byline}</p>\r";
		}
		if(isset($content['body'])) 
		{
			echo $content['body'];
		}
		
		// keywords
		
		if(isset($content['keyword_links'])) 
		{
			echo "<p>{$cms_labels['keywords']} : {$content['keyword_links']}</p>\r";
		}
		?>
		</div>
		<div id="menu" class="float">
		<?php
		if(isset($menu))
		{
			echo "<h4>{$menu_title}</h4>\r";
			echo build_menu($menu,2);
		}
		?>
		</div>
	</div>
</div>
<!-- end page wrapper -->
<!-- start footer -->
<div class="wrap" id="footer"><?php
if (isset($site['copyright']) ) {
	echo "<p>".$site['copyright']."</p>\r";
}
echo $cms_labels['powered_by'];
?></div>
</body>
</html>
