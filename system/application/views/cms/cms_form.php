<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php
if(isset($title_tag))
{
echo $title_tag.' | '; 
}
?>phSite CMS</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="<?php echo BASE_PATH; ?>/assets/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="<?php echo BASE_PATH; ?>/assets/css/fonts.css" rel="stylesheet" type="text/css" media="all" />
<link href="<?php echo BASE_PATH; ?>/assets/css/grid.css" rel="stylesheet" type="text/css" media="screen" />
<link href="<?php echo BASE_PATH; ?>/assets/css/admin.css" rel="stylesheet" type="text/css" media="screen" />
<?php if (isset($custom_header)){
	echo $custom_header;
}
?></head>
<body>
<?php require_once('masthead.php'); ?>
<div class="wrap pad-h">
	<div id="container" class="margins">
		<div id="content" class="column">
			<h1><?php echo $title_h1; ?></h1>
			<?php 
			if (isset($msg)){
				echo "<div class=\"msg\">{$msg}</div>\n";
			}
			if (isset($form)){
				echo $form;
			}
			?>
		</div>
		<div id="menu-secondary" class="column">
			<?php 
			if (isset($sidebar) && $sidebar != '') {
				echo "<div class=\"panel\">\r";
				echo $sidebar;
				echo "</div>\r";
			}
			?>
		</div>
		<div id="menu" class="float">
			<?php 
			if (isset($menu_content) && $menu_content != '') {
				echo "<div class=\"panel\">\r";
				echo '<h3>'.$cms_labels['content']."</h3>\r";
				echo $menu_content;
				echo "</div>\r";
			}

			if (isset($menu_media) && $menu_media != '') {
				echo "<div class=\"panel\">\r";
				echo '<h3>'.$cms_labels['media']."</h3>\r";
				echo $menu_media;
				echo "</div>\r";
			}
			if (isset($menu_setup) && $menu_setup != '') {
				echo "<div class=\"panel\">\r";
				echo '<h3>'.$cms_labels['setup']."</h3>\r";
				echo $menu_setup;
				echo "</div>\r";
			}
			?>
		</div>
	</div>
</div>
<div class="wrap" id="footer"><?php require_once('footer.php'); ?></div>
<?php
require_once('js_includes.php');
if (isset($custom_js)){
	echo $custom_js;
}
?>
</body>
</html>
