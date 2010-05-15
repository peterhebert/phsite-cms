<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Resource not found : <?php echo $site['title'] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="dc.language" scheme="ISO639-3" content="<?php echo $site['lang']; ?>" />
<?php if (isset($site['created_W3C'])) {
	echo "<meta name=\"dcterms.issued\" scheme=\"W3CDTF\" content=\"{$site['created_W3C']}\" />\n";
} ?>
<meta name="dcterms.modified" scheme="W3CDTF" content="<?php echo $site['updated_W3C']; ?>" />
<meta name="dc.title" content="<?php echo $site['title']; ?>" />
<meta name="dc.description" content="<?php echo $site['description']; ?>" />
<meta name="Description" content="<?php echo $site['description']; ?>" />
<?php if ($site['keywords'] != '') { echo "<meta name=\"Keywords\" content=\"{$site['keywords']}\" />\n"; } ?>
<meta name="dc.creator" content="<?php echo $site['creator']; ?>" />
<meta name="robots" content="noindex, nofollow" />

<link href="<?php echo $theme_path; ?>base.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $theme_path; ?>simple_em.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block">
	<h1>Resource not found</h1>
<p>The requested resource
	<?php
	if(isset($uri)) {
	echo ":<br />\r";
	echo $uri;
	echo "<br />\r";
	}
	?>could not be found. Please try the <a href="/sitemap">site map</a> or the search field above.</p>
	</div>
	<div class="page-wrap block" id="footer"><?php if ($site['copyright'] != '') { echo $site['copyright']; } ?>
</div>
</div>
</body>
</html>
