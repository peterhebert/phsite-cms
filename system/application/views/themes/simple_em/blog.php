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
<?php
if(isset($breadcrumb_links))
{
	$breadcrumb = implode(' : ', $breadcrumb_links);
	echo "\t<div class=\"page-wrap block\" id=\"breadcrumb\">{$breadcrumb}</div>\r";
}
?>
	<div class="page-wrap">
		<div class="block five">
			<div class="column w3">
				<?php
				if (isset($title)) {
					echo "<h1 class=\"first\">";
					echo $title;
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
			<div class="float w2">
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
