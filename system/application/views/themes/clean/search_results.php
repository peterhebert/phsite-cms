<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Search Results | <?php echo $site['title'] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="robots" content="noindex,nofollow" />
<meta name="dc.language" scheme="ISO639-3" content="<?php echo $site['lang']; ?>" />
<meta name="dc.title" content="<?php echo $site['title']; ?>" />
<?php
if (isset($site['created_W3C'])) {
	echo "<meta name=\"dcterms.issued\" scheme=\"W3CDTF\" content=\"{$site['created_W3C']}\" />\n";
}
if (isset($site['updated_W3C'])) {
	echo "<meta name=\"dcterms.modified\" scheme=\"W3CDTF\" content=\"{$site['updated_W3C']}\" />\n";
}
echo "<meta name=\"dc.description\" content=\"search results page for '{$site['title']}'\" />\r";
echo "<meta name=\"Description\" content=\"search results page for '{$site['title']}'\" />\r";

if (isset($site['keywords']) && $site['keywords'] != '') {
echo "<meta name=\"Keywords\" content=\"{$site['keywords']}\" />\n";
} 
if (isset($site['creator']) && $site['creator'] != '') {
echo "<meta name=\"dc.creator\" content=\"{$site['creator']}\" />\n";
} 
?>

<link href="<?php echo $theme_path; ?>base.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $theme_path; ?>clean.css" rel="stylesheet" type="text/css" />
</head>
<body>

<?php require_once('masthead.php'); ?>
<!-- start page wrapper -->
<div class="wrap pad-h">
	<div id="container" class="wide margins">
		<h1><?php echo $cms_labels['search_results']; ?></h1>
		<?php 
		if (isset($keyword_search)) {
			echo "<p>".$cms_labels['search_matching_keywords'].' : '.$keyword_search."</p>\r";
		} 

		if(isset($term)) {
			echo "<p>{$term}</p>\r";
			echo "<h2>".$cms_labels['content_results']."</h2>\r"; 
			if(isset($content_results))
			{
				echo ul($content_results);
			}
			else
			{
				echo "<ul><li>".$cms_labels['search_no_results']."</li></ul>\r";
			}
	
			echo "<h2>".$cms_labels['media_results']."</h2>\r"; 
			if(isset($media_results))
			{
				$links = array();
				foreach($media_results as $file)
				{
					if($file['external'] == 1)
					{
						$uri = $file['file_uri'];
					}
					else
					{
						$uri = 'image'.$file['file_uri'];
					}
					$links[] = anchor($uri,$file['title']);
				}
				echo ul($links);
			}
			else
			{
				echo "<ul><li>".$cms_labels['search_no_results']."</li></ul>\r";
			}
		}
		else
		{
			echo "<p>".$cms_labels['search_null']."</p>\r";
		}

		
		?>
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
