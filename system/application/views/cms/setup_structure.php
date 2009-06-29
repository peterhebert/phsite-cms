<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Setup | phSite</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/assets/css/base.css" rel="stylesheet" type="text/css" />
<link href="/assets/css/simple.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb"><?php echo anchor("cms","phSite CMS"); ?> : <?php echo anchor("cms/setup","Setup"); ?> : Data Structure</div>
	<div class="page-wrap block">
		<div class="column w5">
			<h1>Data Structure</h1>
			<p>You can define what types of content your CMS stores, and the individual data fields. Fields can be assinged to any content type.</p>
			<div class="block">
				<div class="column w1"><?php echo $objects; ?></div>
				<div class="float w1"><?php echo $fields; ?></div>
			</div>
		</div>
		<div class="float w1"><?php require_once('setup_menu.php'); ?></div>
	</div>
	<div class="page-wrap block" id="footer"><?php require_once('footer.php'); ?></div>
</div>
</body>
</html>
