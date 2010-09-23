<?php
if(isset($media['title']))
{
	$titleA[] = $media['title'];
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
if(isset($media['title']))
{
	echo "<meta name=\"dc.title\" content=\"{$media['title']}\" />\r";
}
if(isset($media['description']))
{
	echo "<meta name=\"Description\" content=\"{$media['description']}\" />\r";
	echo "<meta name=\"dc.description\" content=\"{$media['description']}\" />\r";
}
if(isset($media['keywords']))
{
	echo "<meta name=\"Keywords\" content=\"{$media['keywords']}\" />\r";
}
else
{
	if(isset($site['keywords']))
	{
		echo "<meta name=\"Keywords\" content=\"{$site['keywords']}\" />\r";
	}
}
if(isset($media['date_created_W3C']))
{
	echo "<meta name=\"dcterms.issued\" scheme=\"W3CDTF\" content=\"{$media['date_created_W3C']}\" />\r";
}
if(isset($media['date_updated_W3C']))
{
	echo "<meta name=\"dcterms.modified\" scheme=\"W3CDTF\" content=\"{$media['date_updated_W3C']}\" />\r";
}
if(isset($media['date_expiry_RFC850']))
{
	echo "<meta http-equiv=\"expires\" content=\"{$media['date_expiry_RFC850']}\">\r";
}

if(isset($media['author']['name_last'] ) && isset($media['author']['name_first'] ) )
{
	$creator = $media['author']['name_last'].', '.$media['author']['name_first'];
	echo "<meta name=\"dc.creator\" content=\"{$creator}\" />\r";
}

?>
<meta name="generator" content="phSite CMS" />