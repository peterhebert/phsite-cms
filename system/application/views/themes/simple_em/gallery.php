<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php
if(isset($html_header))
{
echo $html_header;
}

if(isset($size))
{
?>
<style type="text/css">
<!--
ul.thumbnails li {
min-height: <?php echo round(($size/15)+2.5,3); ?>em;
}
ul.thumbnails li .thumb { width: <?php echo $size + 4 ; ?>px; height: <?php echo $size + 4; ?>px; }
-->
</style>
<?php } ?>
</head>
<body class="gallery">
<?php require_once('masthead.php'); ?>
<div id="page">
	<?php
if(isset($breadcrumb_links))
{
$breadcrumb = implode(' : ', $breadcrumb_links);
echo "\t<div class=\"page-wrap block\" id=\"breadcrumb\">{$breadcrumb}</div>\r";
}
?>
	<div class="page-wrap block">
		<?php
		if (isset($title)) {
			echo "<h1 class=\"first\">";
			echo $title;
			echo "</h1>\r";
		}
		if (isset($content['description'])) {
			echo "<p>";
			echo $content['description'];
			echo "</p>\r";
		}

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
	<div class="page-wrap block" id="footer">
	<?php
	if (isset($site['copyright']) ) {
		echo "<p>".$site['copyright']."</p>\r";
	}
	echo $cms_labels['powered_by'];
	?>
	</div>
</div>
</body>
</html>
