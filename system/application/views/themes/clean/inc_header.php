<?php
if(isset($content['title']))
{
	$titleA[] = $content['title'];
}
if(!isset($home_content_id) || $content['content_id'] != $home_content_id)
{
	$titleA[] = $site['title'];
}

$title = implode(' | ',$titleA);
?>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?php if (isset($theme_path)) {
	echo "<link href=\"{$theme_path}clean";
	if (DEV_MODE == FALSE) {
		//echo '-min';
	}
	echo ".css\" rel=\"stylesheet\" type=\"text/css\" />\r";
} 
?><link href="<?php echo BASE_PATH; ?>/assets/css/jqueryFileTree.css" rel="stylesheet" type="text/css" />
<?php
if(isset($content['title']))
{
	echo "<meta name=\"dc.title\" content=\"{$content['title']}\" />\r";
}
if(isset($content['description']))
{
	echo "<meta name=\"Description\" content=\"{$content['description']}\" />\r";
	echo "<meta name=\"dc.description\" content=\"{$content['description']}\" />\r";
}
if(isset($content['keywords']))
{
	echo "<meta name=\"Keywords\" content=\"{$content['keywords']}\" />\r";
}
else
{
	if(isset($site['keywords']))
	{
		echo "<meta name=\"Keywords\" content=\"{$site['keywords']}\" />\r";
	}
}
if(isset($content['date_created_W3C']))
{
	echo "<meta name=\"dcterms.issued\" scheme=\"W3CDTF\" content=\"{$content['date_created_W3C']}\" />\r";
}
if(isset($content['date_updated_W3C']))
{
	echo "<meta name=\"dcterms.modified\" scheme=\"W3CDTF\" content=\"{$content['date_updated_W3C']}\" />\r";
}
if(isset($content['date_expiry_RFC850']))
{
	echo "<meta http-equiv=\"expires\" content=\"{$content['date_expiry_RFC850']}\">\r";
}

if(isset($content['author']['name_last'] ) && isset($content['author']['name_first'] ) )
{
	$creator = $content['author']['name_last'].', '.$content['author']['name_first'];
	echo "<meta name=\"dc.creator\" content=\"{$creator}\" />\r";
}
if(isset($content['syndication_rss']))
{
	echo "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"{$content['title']}\" href=\"{$content['syndication_rss']}\" />\r";
}
if(isset($content['syndication_atom']))
{
	echo "<link rel=\"alternate\" type=\"application/atom+xml\" title=\"{$content['title']}\" href=\"{$content['syndication_atom']}\" />\r";
}
if(isset($site['syndication_rss']))
{
	$sitewide_rss = sprintf($cms_labels['sitewide_rss'],$site['title']);
	echo "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"{$sitewide_rss}\" href=\"{$site['syndication_rss']}\" />\r";
}
if(isset($site['syndication_atom']))
{
	$sitewide_atom = sprintf($cms_labels['sitewide_atom'],$site['title']);
	echo "<link rel=\"alternate\" type=\"application/atom+xml\" title=\"{$sitewide_atom}\" href=\"{$site['syndication_atom']}\" />\r";
}
?>
<meta name="generator" content="<?php echo $cms_labels['phsite_cms']; ?>" />
<script type="text/javascript" src="<?php echo BASE_PATH; ?>/assets/js/jquery-min.js"></script>
<script type="text/javascript" src="<?php echo BASE_PATH; ?>/assets/js/admin_ui/jquery_external_links.js"></script>
