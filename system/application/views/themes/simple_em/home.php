<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>
<?php
if (isset($site_meta_title)) {
	echo $site_meta_title;
}
?>
</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?php
if (isset($site_meta_lang)) {
	echo "<meta name=\"dc.language\" content=\"{$site_meta_lang}\" />\n";
}
if (isset($site_meta_title)) {
	echo "<meta name=\"dc.title\" content=\"{$site_meta_title}\" />\n";
}

if (isset($site_meta_created_W3C)) {
	echo "<meta name=\"dcterms.issued\" scheme=\"W3CDTF\" content=\"{$site_meta_created_W3C}\" />\n";
}
if (isset($site_meta_updated_W3C)) {
	echo "<meta name=\"dcterms.modified\" scheme=\"W3CDTF\" content=\"{$site_meta_updated_W3C}\" />\n";
}
if (isset($site_meta_description) && $site_meta_description != '') {
	echo "<meta name=\"dc.description\" content=\"{$site_meta_description}\" />\r";
	echo "<meta name=\"Description\" content=\"{$site_meta_description}\" />\r";
}
if (isset($site_meta_keywords) && $site_meta_keywords != '') {
echo "<meta name=\"Keywords\" content=\"{$site_meta_keywords}\" />\n";
} 
if (isset($site_meta_creator) && $site_meta_creator != '') {
echo "<meta name=\"dc.creator\" content=\"{$site_meta_creator}\" />\n";
} 
if (isset($theme_path)) {
echo "<link href=\"{$theme_path}base.css\" rel=\"stylesheet\" type=\"text/css\" />\n";
echo "<link href=\"{$theme_path}simple_em.css\" rel=\"stylesheet\" type=\"text/css\" />\n";
} 

?>
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb">
		<?php
		if (isset($breadcrumb)){
			echo $breadcrumb;
		}
	 ?>
	</div>
	<div class="page-wrap">
		<div class="block five">
			<div class="column w3">
				<?php
				if (isset($recent)) {
					echo "<h2 class=\"first\">";
					echo $cms_labels['recent_content'];
					echo "</h2>\r";
					echo $recent;
				}?>
			</div>
			<div class="float w2">
				<?php
				if (isset($site_meta_description) && $site_meta_description != '') {
					echo "<h2 class=\"first\">";
					echo $cms_labels['about_this_site'];
					echo "</h2>\r";
					echo "<p>{$site_meta_description}</p>\r";
				}

				if(isset($menu) && $menu != '')
				{
					echo "<h3>";
					echo $cms_labels['menu'];
					echo "</h3>\r";
					echo $menu;
				}
				?>
			</div>
		</div>
	</div>
	<div class="page-wrap block" id="footer">
		<?php
	if (isset($site_meta_copyright) ) {
		echo "<p>".$site_meta_copyright."</p>\r";
	}
	echo POWERED_BY;
	?>
	</div>
</div>
</body>
</html>
