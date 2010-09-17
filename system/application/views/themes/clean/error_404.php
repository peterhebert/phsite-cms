<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $header; ?> : <?php echo $site['title'] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="dc.language" scheme="ISO639-3" content="<?php echo $site['lang']; ?>" />
<?php
if (isset($site['created_W3C'])) {
	echo "<meta name=\"dcterms.issued\" scheme=\"W3CDTF\" content=\"{$site['created_W3C']}\" />\n";
}
?>
<meta name="dcterms.modified" scheme="W3CDTF" content="<?php echo $site['updated_W3C']; ?>" />
<meta name="dc.title" content="<?php echo $site['title']; ?>" />
<meta name="dc.description" content="<?php echo $site['description']; ?>" />
<meta name="Description" content="<?php echo $site['description']; ?>" />
<?php
if (isset($site['creator'])) {
	echo "<meta name=\"dcterms.creator\" scheme=\"W3CDTF\" content=\"{$site['creator']}\" />\n";
}
if ($site['keywords'] != '') { echo "<meta name=\"Keywords\" content=\"{$site['keywords']}\" />\n";
}
?>
<meta name="robots" content="noindex, nofollow" />

<link href="<?php echo $theme_path; ?>base.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $theme_path; ?>clean.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php require_once('masthead.php'); ?>
<!-- start page wrapper -->
<div class="wrap pad-h">
	<div id="container">
		<h1><?php echo $header; ?></h1>
		<p><?php echo $message; ?></p>
	</div>
</div>
<!-- end page wrapper -->
<!-- start footer -->
<div class="wrap" id="footer">
<?php
if (isset($site['copyright']) ) {
echo "\t<p>".$site['copyright']."</p>\r";
}
echo $cms_labels['powered_by'];
?>
</div>
<!-- end footer -->
</body>
</html>
