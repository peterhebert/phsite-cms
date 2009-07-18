<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Search Results | <?php echo $site_meta_title ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="robots" content="noindex,nofollow" />
<meta name="dc.language" scheme="ISO639-3" content="<?php echo $site_meta_lang; ?>" />
<meta name="dc.title" content="<?php echo $site_meta_title; ?>" />
<?php
if (isset($site_meta_created_W3C)) {
	echo "<meta name=\"dcterms.issued\" scheme=\"W3CDTF\" content=\"{$site_meta_created_W3C}\" />\n";
}
if (isset($site_meta_updated_W3C)) {
	echo "<meta name=\"dcterms.modified\" scheme=\"W3CDTF\" content=\"{$site_meta_updated_W3C}\" />\n";
}
echo "<meta name=\"dc.description\" content=\"search results page for '{$site_meta_title}'\" />\r";
echo "<meta name=\"Description\" content=\"search results page for '{$site_meta_title}'\" />\r";

if (isset($site_meta_keywords) && $site_meta_keywords != '') {
echo "<meta name=\"Keywords\" content=\"{$site_meta_keywords}\" />\n";
} 
if (isset($site_meta_creator) && $site_meta_creator != '') {
echo "<meta name=\"dc.creator\" content=\"{$site_meta_creator}\" />\n";
} 
?>

<link href="<?php echo $theme_path; ?>base.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $theme_path; ?>simple_em.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb"><?php echo anchor("","Home"); ?> : Search Results</div>
	
	<div class="page-wrap block">
		<h1>Search Results</h1>
		<?php
		if(isset($term)) {
			echo "<p>{$term}</p>\r";
		}

		if(isset($matches)) {
			echo $matches;
		}
		?>
	</div>
	<div class="page-wrap block" id="footer"><?php
	if (isset($site_meta_copyright) ) {
		echo "<p>".$site_meta_copyright."</p>\r";
	}
	echo POWERED_BY;
	?></div>
</div>
</body>
</html>
