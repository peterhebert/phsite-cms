<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $site_meta_title ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="dc.language" scheme="ISO639-3" content="<?php echo $site_meta_lang; ?>" />

<?php if (isset($site_meta_created_W3C)) {
	echo "<meta name=\"dcterms.issued\" scheme=\"W3CDTF\" content=\"{$site_meta_created_W3C}\" />\n";
} ?>
<meta name="dcterms.modified" scheme="W3CDTF" content="<?php echo $site_meta_updated_W3C; ?>" />

<meta name="dc.title" content="<?php echo $site_meta_title; ?>" />
<meta name="dc.description" content="<?php echo $site_meta_description; ?>" />
<meta name="Description" content="<?php echo $site_meta_description; ?>" />
<?php if ($site_meta_keywords != '') { echo "<meta name=\"Keywords\" content=\"{$site_meta_keywords}\" />\n"; } ?>
<meta name="dc.creator" content="<?php echo $site_meta_creator; ?>" />

<link href="<?php echo $theme_path; ?>base.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $theme_path; ?>simple_em.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block">
		<div class="golden">
			<div class="column g1"><?php
			if(isset($recent))
			{
				echo ul($recent, array('class' => 'posts'));
			}
			?>
			</div>
			<div class="float g2">
				<?php
				if(isset($sidebar))
				{
					echo ul($sidebar, array('class' => 'posts'));
				}
				?>
				<div class="block bdr-top">
					<h2>Site Menu</h2>
					<?php echo $menu; ?>
				</div>
			</div>
		</div>
		
	</div>
	<div class="page-wrap block" id="footer"><?php if ($site_meta_copyright != '') { echo $site_meta_copyright; } ?></div>
</div>
</body>
</html>
