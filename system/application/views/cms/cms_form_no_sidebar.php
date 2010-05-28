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
<link href="<?php echo BASE_PATH; ?>/assets/css/base.css" rel="stylesheet" type="text/css" />
<link href="<?php echo BASE_PATH; ?>/assets/css/simple.css" rel="stylesheet" type="text/css" />
<?php if (isset($custom_header)){
	echo $custom_header;
}
?></head>
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
	<div class="page-wrap block">
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
