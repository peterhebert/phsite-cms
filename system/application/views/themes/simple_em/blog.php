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
	<div class="page-wrap block" id="breadcrumb">
		<?php
		if (isset($breadcrumb)){
			echo $breadcrumb;
		}
	 ?>
	</div>
	<div class="page-wrap">
		<div class="block five">
			<div class="column w3">
				<?php
				if (isset($title)) {
					echo "<h1 class=\"first\">";
					echo $title;
					echo "</h1>\r";
				}
				if (isset($content_description)) {
					echo "<p>";
					echo $content_description;
					echo "</p>\r";
				}
				if (isset($blog)) {
					echo $blog;
				}
				?>
			</div>
			<div class="float w2">
				<?php
				if (isset($site_meta_description) && $site_meta_description != '') {
					echo "<h2 class=\"first\">";
					echo $cms_labels['about_this_site'];
					echo "</h2>\r";
					echo "<p>{$site_meta_description}</p>\r";
				}

				if(isset($menu) && $menu != '')
				{
					echo "<h3>";
					echo $cms_labels['menu'];
					echo "</h3>\r";
					echo $menu;
				}
				?>
			</div>
		</div>
	</div>
	<div class="page-wrap block" id="footer">
		<?php
	if (isset($site_meta_copyright) ) {
		echo "<p>".$site_meta_copyright."</p>\r";
	}
	echo POWERED_BY;
	?>
	</div>
</div>
</body>
</html>
