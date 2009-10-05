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
			<h1 class="first"><?php echo $cms_labels['setup']; ?></h1>
			<div class="column w3">
				<h2><?php echo $cms_labels['data_structure']; ?></h2>
				<p><?php echo $cms_labels['blurb_data_structure']; ?></p>
				<ul>
					<li><?php echo anchor("cms/setup/content_types",$cms_labels['edit_content_types']); ?></li>
					<li><?php echo anchor("cms/setup/media_types",$cms_labels['edit_media_types']); ?></li>
					<li><?php echo anchor("cms/setup/fields",$cms_labels['edit_fields']); ?></li>
				</ul>

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
			<div class="float w3">
				<h2><?php echo $cms_labels['site_prefs']; ?></h2>
				<p><?php echo $cms_labels['blurb_site_prefs']; ?></p>
				<p><?php echo anchor("cms/setup/site_preferences",$cms_labels['site_prefs']); ?></p>

				<h2><?php echo $cms_labels['site_metadata']; ?></h2>
				<p><?php echo $cms_labels['blurb_site_metadata']; ?></p>
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
