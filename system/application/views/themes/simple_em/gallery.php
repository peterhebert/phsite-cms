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
<body class="">
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

		if(isset($subdirs) && count($subdirs) > 0)
		{
			echo "<p>{$cms_labels['media_subdirs']}</p>";
			echo ul($subdirs);
		}
		
		if(isset($attachments))
		{
			echo "<pre>";
			print_r($attachments);
			echo "</pre>\r";

			echo "<ul class=\"thumbnails\">";
			
			foreach($attachments as $file)
			{
				if($file['mime_cat'] == 'image')
				{
				}
				/*
[0] => Array
        (
            [a_id] => 3
            [content_id] => 4
            [media_id] => 1
            [file_uri] => /hockey/20030101_Friday-night-hockey_Goalie-Pete-3.JPG
            [external] => 0
            [mime_type] => image/jpeg
            [mime_cat] => image
            [ext] => jpg
            [filename] => 20030101_Friday-night-hockey_Goalie-Pete-3.JPG
            [date_created] => 2003-01-01 12:04:25
            [publish] => 1
            [title] => Goalie Pete 3
            [uri_title] => goalie-pete-3
            [subtitle] => 
            [keywords] => goalie, Hockey, Peter Hebert, sports
        )
				*/
$file_link = MEDIA_PATH_ORIG.$file['file_uri'];
$file_full = ROOT_REL.$file_link;
				echo "<li>\r";
				echo "<div class=\"thumb\">";
				echo "<a href=\"{$file_link}\">";
				echo view_image($file_full,$this->Site_model->img_size_th,array('title' => $file['title']));
				echo "</a></div>\r";
				echo "</li>\r";
			}
			echo "</ul>";
		}

		if(isset($content['dir_listing']))
		{
			echo "<pre>";
			print_r($content['dir_listing']);
			echo "</pre>\r";
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
