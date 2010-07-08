<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Setup | phSite</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="<?php echo BASE_PATH; ?>/assets/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="<?php echo BASE_PATH; ?>/assets/css/fonts.css" rel="stylesheet" type="text/css" media="all" />
<link href="<?php echo BASE_PATH; ?>/assets/css/grid.css" rel="stylesheet" type="text/css" media="screen" />
<link href="<?php echo BASE_PATH; ?>/assets/css/admin.css" rel="stylesheet" type="text/css" media="screen" />
<link href="<?php echo BASE_PATH; ?>/assets/css/admin-mobile.css" rel="stylesheet" type="text/css" media="mobile" />
<link href="<?php echo BASE_PATH; ?>/assets/css/jqueryFileTree.css" rel="stylesheet" type="text/css" />
<?php if (isset($custom_header)){
	echo $custom_header;
}
?>
</head>
<body>
<?php require_once('masthead.php'); ?>
<div class="wrap pad-h">
	<h1><?php echo $cms_labels['setup']; ?></h1>
	<div class="block margins">
		<div class="column w4">
			<h2><?php echo $cms_labels['data_structure']; ?></h2>
			<p><?php echo $cms_labels['blurb_data_structure']; ?></p>
			<ul>
				<li><?php echo anchor("cms/setup/content_types",$cms_labels['content_types']); ?></li>
				<li><?php echo anchor("cms/setup/media_types",$cms_labels['media_types']); ?></li>
				<li><?php echo anchor("cms/setup/fields",$cms_labels['fields']); ?></li>
				<li><?php echo anchor("cms/setup/lists",$cms_labels['lists']); ?></li>
			</ul>
		</div>
		<div class="column w4">
			<h2><?php echo $cms_labels['site_prefs']; ?></h2>
			<p><?php echo $cms_labels['blurb_site_prefs']; ?></p>
			<p><?php echo anchor("cms/setup/site_preferences",$cms_labels['site_prefs']); ?></p>
			<h2><?php echo $cms_labels['site_metadata']; ?></h2>
			<p><?php echo $cms_labels['blurb_site_metadata']; ?></p>
			<ul>
				<li><?php echo $cms_labels['site_metadata_edit']; ?> <?php echo $site_metadata; ?> </li>
				<li><?php echo anchor("cms/setup/site_meta_fields",$cms_labels['site_meta_fields']); ?></li>
			</ul>
		</div>
		<div class="float w5">
			<h2><?php echo $cms_labels['languages']; ?></h2>
			<p><?php echo $cms_labels['blurb_choose_langs']; ?></p>
			<p><?php echo anchor("cms/setup/languages",$cms_labels['edit_languages'] ); ?></p>
			<h2><?php echo $cms_labels['users']; ?></h2>
			<p><?php echo $cms_labels['blurb_users']; ?></p>
			<ul>
				<li><?php echo anchor("cms/user",$cms_labels['users_edit']); ?></li>
				<li><?php echo anchor("cms/user/user_meta_fields",$cms_labels['users_meta_fields']); ?></li>
			</ul>
		</div>
	</div>
</div>
<div class="wrap" id="footer">
	<?php require_once('footer.php'); ?>
</div>
</body>
</html>
