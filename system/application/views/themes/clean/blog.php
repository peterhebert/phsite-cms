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
		<div id="content" class="column">
			<?php
			if (isset($content['title'])) {
				echo "<h1 class=\"first\">";
				echo $content['title'];
				echo "</h1>\r";
			}
			if (isset($subtitle) && $subtitle != '') {
				echo "<h2>".$subtitle."</h2>\r";
			}
			if (isset($content['description'])) {
				echo "<p>".$content['description']."</p>\r";
			}
			if (isset($blog)) {
				echo $blog;
			}
			?>
		</div>
		<div id="menu" class="float">
				<?php
				if(isset($menu) && $menu != '')
				{
					if(isset($menu_title))
					{
						echo "<h3>{$menu_title}</h3>\r";
					}
					echo $menu;
				}
				?>
		</div>
	</div>
</div>
<!-- end page wrapper -->
<!-- start footer -->
<div class="wrap" id="footer">
	<?php
	if (isset($site['copyright']) ) {
		echo "<p>".$site['copyright']."</p>\r";
	}
	echo $cms_labels['powered_by'];
	?>
</div>
<!-- end footer -->
</body>
</html>
