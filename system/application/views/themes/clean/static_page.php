<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php
if (isset($html_header)) {
	echo($html_header);
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
			if(isset($content['subtitle']))
			{
				echo "<h2>{$content['subtitle']}</h2>\r";
			}
			if(isset($content['body']))
			{
				echo $content['body'];
			}


		?>
		</div>
		<div id="menu" class="float">
		<?php require_once('inc_attachments.php');

		if(isset($menu) && count($menu) > 0)
		{
			$skip = NULL;
			if($content['parent_id'] > 0)
			{
				$skip = $content['content_id'];
			}
			$menutxt = trim(build_menu($menu,2,$skip));
			
			if($menutxt != '')
			{
				echo "<h4>{$menu_title}</h4>\r";
				echo $menutxt;
			}
		}
		?>
		</div>
	</div>
</div>
<!-- end page wrapper -->
<?php require_once('inc_footer.php'); ?>
</body>
</html>