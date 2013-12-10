<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head><?php
$header_titles = array();
if(isset($title_tag))
{
	$header_titles[] = $title_tag;
}
$header_titles[] = $cms_labels['search_results'];
$header_titles[] = $cms_labels['phsite_cms'];
if (isset($site['title'])) {
	$header_titles[] = $site['title'];
}
$title_tag = implode(' | ',$header_titles); 
?>
<title><?php echo $title_tag; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="<?php echo BASE_PATH; ?>/assets/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="<?php echo BASE_PATH; ?>/assets/css/fonts.css" rel="stylesheet" type="text/css" media="all" />
<link href="<?php echo BASE_PATH; ?>/assets/css/grid.css" rel="stylesheet" type="text/css" media="screen" />
<link href="<?php echo BASE_PATH; ?>/assets/css/admin.css" rel="stylesheet" type="text/css" media="screen" />
<?php if (isset($custom_header)){
	echo $custom_header;
}
?></head>
<body>
<?php require_once('masthead.php'); ?>
<div class="wrap pad-h">
	<div id="container" class="margins">
		<div id="content">
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
	</div>
</div>
<div class="wrap" id="footer"><?php require_once('footer.php'); ?></div>
<?php
require_once('js_includes.php');
if (isset($custom_js)){
	echo $custom_js;
}
?>
</body>
</html>
