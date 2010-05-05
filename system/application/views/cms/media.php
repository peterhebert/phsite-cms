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
<link href="<?php echo $base_path; ?>/assets/css/base.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $base_path; ?>/assets/css/simple.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="dialog" title="Delete the selected items?" class="hide">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>These items will be permanently deleted and cannot be recovered. Are you sure?</p>
</div>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb">
	<?php
		if (isset($breadcrumb)){
			echo $breadcrumb;
		}
	 ?>
	</div>
	<div class="page-wrap block">
		<div class="column w5">
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
		<div class="float w1">
			<?php 
			if (isset($sidebar)) {
				echo $sidebar;
			}
			?>
		</div>

	</div>
	<div class="page-wrap block" id="footer"><?php require_once('footer.php'); ?></div>
</div>
<?php
require_once('js_includes.php');
if (isset($custom_js)){
	echo $custom_js;
}
?>
</body>
</html>
