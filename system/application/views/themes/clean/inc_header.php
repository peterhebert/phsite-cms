<?php
if(isset($content['title']))
{
	$titleA[] = $content['title'];
}
$titleA[] = $site['title'];
$title = implode(' | ',$titleA);
?>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?php if (isset($theme_path)) {
	echo "<link href=\"{$theme_path}clean";
	if (!DEV_MODE) {
		echo '-min';
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

?>
<meta name="generator" content="phSite CMS" />