<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
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
<style type="text/css">
/* <![CDATA[ */
ul.thumbnails li {
	min-height: <?php echo round(($size/15)+2.5,3); ?>em;
}
ul.thumbnails li .thumb { width: <?php echo $size + 4 ; ?>px; height: <?php echo $size + 4; ?>px; }
/* ]]> */
</style>
</head>
<body class="gallery">
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb"><?php echo $breadcrumb; ?></div>
	<div class="page-wrap block">
		<h1><?php echo $title_h1; ?></h1>
		<?php 
		if(count($subdirs) > 0)
		{
			echo "<p>Sub-directories:</p>";
			echo ul($subdirs);
		}
		
		if(isset($files) && is_array($files))
		{
			echo "<ul class=\"thumbnails\">";
			
			foreach($files as $file)
			{
				$link = str_replace($media_root,'',$file);
				echo "<li>\r";
				echo "<div class=\"thumb\">";
				echo "<a href=\"/gallery/media{$link}\">";
				echo view_image($file,$this->Site_model->img_size_th,array('title' => 'image title goes here'));
				echo "</a></div>\r";
				echo "</li>\r";
			}
			echo "</ul>";
		}
		?>
	</div>
	<div class="page-wrap block" id="footer"><?php 
	echo "<p>";
	echo $site_meta_copyright;
	echo "</p>\r";
	echo POWERED_BY;
	?></div>
</div>
</body>
</html>
