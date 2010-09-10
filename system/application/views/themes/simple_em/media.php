<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php
if(isset($html_header))
{
echo $html_header;
}
$size = $this->Site_model->img_size_th;
?>
<style type="text/css">
<!--
ul.thumbnails li {
min-height: <?php echo round(($size/15)+2.5,3); ?>em;
}
ul.thumbnails li .thumb { width: <?php echo $size + 4 ; ?>px; height: <?php echo $size + 4; ?>px; }
-->
</style>

<!-- jQuery lightBox plugin -->
<script type="text/javascript" src="<?php echo BASE_PATH; ?>/assets/js/jquery-min.js"></script>
<script type="text/javascript" src="<?php echo BASE_PATH; ?>/assets/js/jquery.lightbox-0.5.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo BASE_PATH; ?>/assets/css/jquery.lightbox-0.5.css" media="screen" />
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
			if(isset($content['title']))
			{
				echo "<h1 class=\"align-center\">{$content['title']}</h1>\r";
			}
			if(isset($content['subtitle']))
			{
				echo "<h2>{$content['subtitle']}</h2>\r";
			}
			if(isset($content['mime_cat']) && $content['mime_cat'] == 'image')
			{
				echo "<div class=\"image image-center\" style=\"width: {$this->Site_model->img_size_lg}px\">";
				echo view_image($content['server_path'],$this->Site_model->img_size_lg);
				echo "</div>\r";
			}

			if(isset($content['description']))
			{
				echo "<div class=\"block\">{$content['description']}</div>\r";
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
