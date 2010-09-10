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
		
		$files = array();
		if(isset($attachments))
		{
			$files = array_merge($files,$attachments);
		}

		if(isset($content['dir_listing']))
		{
			$files = array_merge($files,$content['dir_listing']['files']);
		}
		$this->lang->load('mime',$this->Content_model->lang_CI_Name);
		$mime_lang = $this->lang->line('mime');
		
		if(count($files) > 0)
		{
		
			echo "<ul class=\"thumbnails\">";

			foreach($files as $file)
			{
				if($file['external'] == 0)
				{
					$file_full = ROOT_REL.MEDIA_PATH_ORIG.$file['file_uri'];
					$file_link = view_image($file_full,$this->Site_model->img_size_lg,array('title' => $title),FALSE,TRUE);
				}
				else
				{
					$file_link = $file['file_uri'];
					$file_full = $file_link;
				}

				if($file['title'] != '')
				{
					$title = $file['title'];
				}
				else
				{
					$title = $file['filename'];
				}

				echo "<li>";
				if($file['external'] == 0)
				{
					echo "<div class=\"thumb\"><a href=\"{$file_link}\" title=\"{$file['title']}\" rel=\"{$file['mime_cat']}\">";
					if($file['mime_cat'] == 'image')
					{
						echo view_image($file_full,$this->Site_model->img_size_th,array('title' => $title));
					}
					else
					{
						echo $title;
					}

					echo "</a></div>\r";
				}
				else
				{
					echo "<a href=\"{$file_link}\">{$title}</a> ({$mime_lang[$file['extension']]})";
				}
				echo "</li>\r";
			}
			echo "</ul>\r";
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
<!-- init jQuery lightBox plugin -->
<script type="text/javascript">
/* <![CDATA[ */

$(function() {
	$("ul.thumbnails a").lightBox();
});

/* ]]> */</script>
<!-- / end jQuery lightBox plugin -->
</body>
</html>
