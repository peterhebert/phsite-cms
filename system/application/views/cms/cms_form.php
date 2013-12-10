<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head><?php
$header_titles = array();
if(isset($title_tag))
{
	$header_titles[] = $title_tag;
}
$header_titles[] = $cms_labels['phsite_cms'];
if (isset($site['title'])) {
	$header_titles[] = $site['title'];
}
$title_tag = implode(' | ',$header_titles); 
?>
<title><?php echo $title_tag; ?></title>
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
				if (isset($sidebar_panel) && $sidebar_panel == true) {
					echo "<div class=\"panel\">\r";
				}
				echo $sidebar;
				if (isset($sidebar_panel) && $sidebar_panel == true) {
					echo "</div>\r";
				}
			}
			?>
			&nbsp;
		</div>
		<div id="menu" class="float">
			<?php 
			if (isset($menu_order))
			{
				foreach($menu_order as $menu_label)
				{
					if (isset($menu[$menu_label]) && $menu[$menu_label] != '') {
						echo "<div class=\"panel\">\r";
						echo '<h3>'.$cms_labels[$menu_label]."</h3>\r";
						echo $menu[$menu_label];
						echo "</div>\r";
					}
				}
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
