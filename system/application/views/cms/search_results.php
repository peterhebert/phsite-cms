<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Search Results | phSite</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="<?php echo BASE_PATH; ?>/assets/css/base.css" rel="stylesheet" type="text/css" />
<link href="<?php echo BASE_PATH; ?>/assets/css/simple.css" rel="stylesheet" type="text/css" />
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
	<div class="page-wrap block" id="footer"><?php require_once('footer.php'); ?></div>
</div>
</body>
</html>
