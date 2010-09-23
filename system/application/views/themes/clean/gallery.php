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
</head>
<body>
<?php require_once('masthead.php'); ?>
<!-- start page wrapper -->
<div class="wrap pad-h">
	<div id="container">
		<?php
		if (isset($content['title'])) {
			echo "<h1>{$content['title']}</h1>\r";
		}
		if (isset($content['description'])) {
			echo "<p>";
			echo $content['description'];
			echo "</p>\r";
		}

		
		$files = array();
		if(isset($attachments))
		{
			$files = array_merge($files,$attachments);
		}

		if(isset($content['dir_listing']))
		{
			$files = array_merge($files,$content['dir_listing']);
		}
		if(isset($content['subdirs']) && count($content['subdirs']) > 0)
		{
			echo "<p>{$cms_labels['media_subdirs']}</p>";
			//echo ul($subdirs);
			echo "<pre>";
			print_r($subdirs);
			echo "</pre>\r";
		}

		$this->lang->load('mime',$this->Content_model->lang_CI_Name);
		$mime_lang = $this->lang->line('mime');
		
		if(count($files) > 0)
		{
		
			echo "<ul class=\"thumbnails\">";


			foreach($files as $file)
			{
				if($file['external'] == 1)
				{
					$file_link = $file['file_uri'];
					$file_full = $file['file_uri'];
				}
				else
				{
					$file_link = $resource_uri.'/view'.$file['file_uri'];
					$file_full = ROOT_REL.MEDIA_PATH_ORIG.$file['file_uri'];
				}

				if($file['title'] != '')
				{
					$title = $file['title'];
				}
				else
				{
					$title = $file['filename'];
				}
				$text = $title;

				if($file['external'] == 0)
				{
					$poster_filename = $file['path_basename'].'.jpg';
					if(in_array($file['mime_type'],$image_types))
					{
						$text = "<div class=\"thumb\"><a href=\"{$file_link}\" title=\"{$title}\" rel=\"{$file['mime_cat']}\">";
						$text .= view_image($file_full,$this->Site_model->img_size_th,array('title' => $title));
						$text .= "</a></div>\r";
					}
					elseif(file_exists($poster_filename))
					{
						$text = "<div class=\"thumb\"><a href=\"{$file_link}\" title=\"{$title}\" rel=\"{$file['mime_cat']}\">";
						$text .= view_image($poster_filename,$this->Site_model->img_size_th,array('title' => $title));
						$text .= "</a></div>\r";
					}
					else
					{
						$text = "<a href=\"{$file_link}\" title=\"{$title}\" rel=\"{$file['mime_cat']}\">{$title}</a>";
					}
					
				}
				else
				{
						$text = "<a href=\"{$file_link}\" title=\"{$title}\" rel=\"{$file['mime_cat']}\">{$title}</a>";
				}

				echo "<li>{$text}</li>\r";
			}
			echo "</ul>\r";
		}

?>
	</div>
</div>
<!-- end page wrapper -->
<!-- start footer -->
<div class="wrap" id="footer"><?php
if (isset($site['copyright']) ) {
	echo "<p>".$site['copyright']."</p>\r";
}
echo $cms_labels['powered_by'];
?></div>
</body>
</html>
