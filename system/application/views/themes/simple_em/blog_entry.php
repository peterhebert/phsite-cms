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
	<div class="page-wrap">
		<div class="block five">
			<div class="column w3">
<?php
if(isset($title)) 
{
	echo "<h1>{$title}</h1>\r";
}
if(isset($subtitle) && $subtitle != '') 
{
	echo "<h2>{$subtitle}</h2>\r";
}

if(isset($author)) 
{
	$user_name_formatted = $author['name_first'].' '.$author['name_last'];
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
	echo "<p>{$content['keyword_links']}</p>\r";
}

?>
			</div>
			<div class="float w2">
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
	<div class="page-wrap block" id="footer">
		<?php
	if (isset($site['copyright']) ) {
		echo "<p>".$site['copyright']."</p>\r";
	}
	echo $cms_labels['powered_by'];
	?>
	</div>
</div>
</body>
</html>
