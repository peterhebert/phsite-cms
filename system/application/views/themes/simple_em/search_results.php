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
<?php if (isset($breadcrumb) && is_array($breadcrumb) && count($breadcrumb) > 0) {
	$bc = implode(' : ',$breadcrumb);
?>
<div class="page-wrap block" id="breadcrumb"><?php echo $bc; ?></div>
<?php } ?>

	
	<div class="page-wrap block">
		<h1><?php echo $cms_labels['search_results']; ?></h1>
		<?php 
		if (isset($keyword_search)) {
			echo "<p>".$cms_labels['search_matching_keywords'].' : '.$keyword_search."</p>\r";
		} 

		if(isset($term)) {
			echo "<p>{$term}</p>\r";
			if(!isset($content_results) && !isset($media_results))
			{
				echo "<p>".$cms_labels['search_no_results']."</p>\r";
			}
		}
		else
		{
			echo "<p>".$cms_labels['search_null']."</p>\r";
		}

		if(isset($content_results))
		{
			echo "<h2>".$cms_labels['content_results']."</h2>\r"; 
			echo ul($content_results);
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
