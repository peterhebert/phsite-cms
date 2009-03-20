<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Setup | phSite</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/css/base.css" rel="stylesheet" type="text/css" />
<link href="/css/simple.css" rel="stylesheet" type="text/css" />
</head>
<body>
	<?php require_once('masthead.php'); ?>
	<div id="page">
		<div class="page-wrap block" id="breadcrumb"><?php echo anchor("cms","phSite CMS"); ?> : Setup</div>
		<div class="page-wrap block">
			<h1 class="first">Setup</h1>
			<div class="column w3">
				<h2>Site preferences</h2>
				<p>This is where you can change site-wide prefrences, such as designating a home page, choosing default languages, designating a Webmaster and Editor users, and selecting a site theme.</p>
				<p><?php echo anchor("cms/setup/site_prefs","Edit site preferences"); ?></p>
				<h2>Data Structure</h2>
				<p>You can define what types of content your site has, and add as many custom fields as you want.</p>
				<p><?php echo anchor("cms/setup/structure","Edit data structure"); ?></p>
	
			</div>
			<div class="float w3">
				<h2>Languages</h2>
				<p>Choose what languages you want to use on your site.</p>
				<p><?php echo anchor("cms/setup/languages","Edit languages"); ?></p>
				<h2>Users</h2>
				<p>Manage the users of your site</p>
				<p><?php echo anchor("cms/setup/users","Edit users"); ?></p>
			</div>
		</div>
		<div class="page-wrap block" id="footer"><?php require_once('footer.php'); ?></div>
	</div>

</body>
</html>
