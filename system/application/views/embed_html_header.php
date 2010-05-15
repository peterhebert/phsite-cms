<title><?php
if(isset($title))
{
echo $title.' | ';
}
echo $site['title'];
?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="<?php echo $theme_path; ?>base.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $theme_path; ?>simple_em.css" rel="stylesheet" type="text/css" />
<?php
if(isset($title))
{
echo "<meta name=\"dc.title\" content=\"{$title}\" />\r";
}

if(isset($content['description']))
{
echo "<meta name=\"Description\" content=\"{$content['description']}\" />\r";
echo "<meta name=\"dc.description\" content=\"{$content['description']}\" />\r";
}

if(isset($keywords))
{
echo "<meta name=\"Keywords\" content=\"{$keywords}\" />\r";
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

/*
<meta name=\"review_date\" content=\"2010-07-16\" />

<meta name=\"dc.creator\" content=\"\" />
<meta name=\"dc.contributor\" content=\"\" />
*/

?>
<meta name="generator" content="phSite CMS" />

