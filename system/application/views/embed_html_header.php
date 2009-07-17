<title><?php
if(isset($title))
{
echo $title.' | ';
}
echo $site_meta_title;
?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="<?php echo $theme_path; ?>base.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $theme_path; ?>simple_em.css" rel="stylesheet" type="text/css" />
<?php
if(isset($title))
{
echo "<meta name=\"dc.title\" content=\"{$title}\" />\r";
}

if(isset($content_description))
{
echo "<meta name=\"Description\" content=\"{$content_description}\" />\r";
echo "<meta name=\"dc.description\" content=\"{$content_description}\" />\r";
}

if(isset($content_keywords))
{
echo "<meta name=\"Keywords\" content=\"{$content_keywords}\" />\r";
}
else
{
	if(isset($site_meta_keywords))
	{
	echo "<meta name=\"Keywords\" content=\"{$site_meta_keywords}\" />\r";
	}
}


/*
<meta name=\"dcterms.issued\" scheme=\"W3CDTF\" content=\"2008-12-11\" />

<meta name=\"dcterms.modified\" scheme=\"W3CDTF\" content=\"2009-07-16\" />
<meta name=\"review_date\" content=\"2010-07-16\" />


<meta name=\"dc.creator\" content=\"\" />
<meta name=\"dc.contributor\" content=\"\" />
*/
?>

