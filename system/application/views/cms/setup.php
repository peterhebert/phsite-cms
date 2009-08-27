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
		<div class="page-wrap block" id="breadcrumb"><?php echo $breadcrumb; ?></div>
		<div class="page-wrap block">
			<h1 class="first">Setup</h1>
			<div class="column w3">
				<h2>Data Structure</h2>
				<p>You can define what types of content your site has, and add as many custom fields as you want.</p>
				<ul>
					<li><?php echo anchor("cms/setup/content_types","Edit content types"); ?></li>
					<li><?php echo anchor("cms/setup/fields","Edit fields"); ?></li>
				</ul>

				<h2>Languages</h2>
				<p>Choose what languages you want to use on your site.</p>
				<p><?php echo anchor("cms/setup/languages","Edit languages"); ?></p>
				<h2>Users</h2>
				<p>Manage the users of your site</p>
				<ul>
					<li><?php echo anchor("cms/user",$cms_labels['users_edit']); ?></li>
					<li><?php echo anchor("cms/user/user_meta_fields",$cms_labels['users_meta_fields']); ?></li>
				</ul>


			</div>
			<div class="float w3">
				<h2>Site preferences</h2>
				<p>This is where you can change site-wide prefrences, such as designating a home page, choosing default languages, designating a Webmaster and Editor users, and selecting a site theme.</p>
				<p><?php echo anchor("cms/setup/site_preferences",$cms_labels['site_prefs']); ?></p>
				<h2>Site metadata</h2>
				<p>This is where you can change information about your site, such as description, title, footer, etc.</p>
				<ul>
					<li><?php echo $cms_labels['site_metadata_edit']; ?>
						<?php echo $site_metadata; ?>
					</li>
					<li><?php echo anchor("cms/setup/site_meta_fields",$cms_labels['site_meta_fields']); ?></li>
				</ul>

			</div>
		</div>
		<div class="page-wrap block" id="footer"><?php require_once('footer.php'); ?></div>
	</div>

</body>
</html>
