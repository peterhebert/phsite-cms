<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php
if(isset($html_header))
{
echo $html_header;
}
?>
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
<?php
if(isset($breadcrumb_links))
{
	$breadcrumb = implode(' : ', $breadcrumb_links);
	echo "\t<div class=\"page-wrap block\" id=\"breadcrumb\">{$breadcrumb}</div>\r";
}
if(isset($other_langs) && count($other_langs) > 0)
{
	foreach ($other_langs as $lang)
	{
		if($lang['lang_Native_Name'] != '')
		{
			$langlinks[] = anchor($lang['uri'], $lang['lang_Native_Name']);
		}
		else
		{
			$langlinks[] = anchor($lang['uri'], $lang['lang_Ref_Name']);
		}
	}
	$languages = implode(' | ', $langlinks);
	echo "\t<div class=\"page-wrap block\" id=\"languages\">{$cms_labels['languages_other']} : {$languages}</div>\r";
}

?>
	<div class="page-wrap">
		<div class="block five">
			<div class="column w3">
<?php
if(isset($title)) 
{
	echo "<h1>{$title}</h1>\r";
}
if(isset($subtitle) && $subtitle != '') 
{
	echo "<h2>{$subtitle}</h2>\r";
}

if(isset($author)) 
{
	$user_name_formatted = $author['name_first'].' '.$author['name_last'];
	$byline = sprintf($cms_labels['posted_by'],$content_date_created,$user_name_formatted);
	echo "<p>{$byline}</p>\r";
}

if(isset($content_body)) 
{
	echo $content_body;
}

// keywords

if(isset($content_keyword_links)) 
{
	echo "<p>{$content_keyword_links}</p>\r";
}

?>
			</div>
			<div class="float w2">
				<?php
				if(isset($menu_title))
				{
					echo "<h3>{$menu_title}</h3>\r";
				}
				if(isset($menu) && $menu != '')
				{
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
	echo $cms_labels['powered_by'];
	?>
	</div>
</div>
</body>
</html>
