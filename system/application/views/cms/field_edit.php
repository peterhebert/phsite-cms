<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Field editor | phSite CMS</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/assets/css/base.css" rel="stylesheet" type="text/css" />
<link href="/assets/css/simple.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb">
	<?php echo anchor("cms","phSite CMS"); ?> : 
	<?php echo anchor("cms/setup","Setup"); ?> : 
	<?php echo anchor("cms/setup/structure","Data Structure"); ?> : 
	Field editor</div>
	<div class="page-wrap">
		<div class="block">
			<div class="column w4">
			<h1 class="first">Field editor</h1>
			<?php 
			if (isset($msg)) {
				echo "<div class=\"msg\">$msg</div>\n"; 
			}
			if (isset($form)) {
				echo $form; 
			}
			?></div>
			<div class="float w2">&nbsp;</div>
		</div>
	</div>
	<div class="page-wrap block" id="footer"><?php require_once('footer.php'); ?></div>
</div>
</body>
</html>
