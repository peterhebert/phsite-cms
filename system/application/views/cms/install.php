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
<div id="masthead" class="float block">
	<div class="wrap padded">
		<div class="column" id="site-id">
			<h6><?php echo anchor("http://phsite.peterhebert.com",$cms_labels['phsite_cms'],array('rel' => 'external')) ?></h6>
			<p><?php echo $cms_labels['slogan']; ?></p>
		</div>
		<div class="column" id="site-admin">
			<p><?php echo $title_h1; ?></p>
		</div>
	</div>
</div>
<div class="wrap pad-h">
	<div id="container" class="wide margins">
		<div id="content" class="column">
			<h1><?php echo $title_h1; ?></h1>
			<?php 
			if (isset($steps) && $installer_step > 0){
				echo "<ol class=\"steps block\">\r";
				foreach($steps as $index => $step)
				{
					echo "\t<li";
					if($installer_step == ($index + 1))
					{
						echo " class=\"current\"";
					}
					echo ">{$step}</li>\r";
				}
				echo "</ol>\r";
			}
			if (isset($msg)){
				echo "<div class=\"msg\">{$msg}</div>\r";
			}
			if (isset($form)){
				echo $form;
			}
			?>
		</div>
		<div id="menu" class="float">
			<?php 
			if (isset($sidebar) && $sidebar != '') {
				echo "<div class=\"panel\">\r";
				echo $sidebar;
				echo "</div>\r";
			}

			?>
		</div>
	</div>
</div>
<div class="wrap" id="footer"><?php require_once('footer.php'); ?></div>
<?php
require_once('js_installer.php');
if (isset($custom_js)){
	echo $custom_js;
}
?>
</body>
</html>
