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
<body class="gallery">
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block">
		<?php
			if(isset($msg))
			{
				echo "<div class=\"msg\">{$msg}</div>\r";
			}

			if(isset($media['title']))
			{
				echo "<h1 class=\"align-center\">{$media['title']}</h1>\r";
			}
			if(isset($media['subtitle']) && $media['subtitle'] != '')
			{
				echo "<h2>{$media['subtitle']}</h2>\r";
			}

			if($media['external'] == 1)
			{
				$src = $media['file_uri'];
			}
			else
			{
				$src = $media['rel_path'];
			}
			
			if(in_array($media['mime_type'],$image_types))
			{
				echo "<div class=\"image image-center\" style=\"width: {$this->Site_model->img_size_lg}px\">";
				echo view_image($media['server_path'],$this->Site_model->img_size_lg);
				echo "</div>\r";
			}
			
			if (in_array($media['mime_type'],$playable_types))
			{
				$params = array(
				'src' => $src
				);
				echo "<div class=\"image image-center\">";
				echo media_player_object($params);
				echo "</div>\r";
			}

			if(isset($media['description']))
			{
				echo "<div class=\"block\">{$media['description']}</div>\r";
			}
			if(isset($media['creator'])) 
			{
				echo "<p>".sprintf($cms_labels['media_credit'],$media['creator'])."</p>\r";
			}
			if(isset($media['keyword_links'])) 
			{
				echo "<p>{$cms_labels['keywords']} : {$media['keyword_links']}</p>\r";
			}
			// download link
			if(isset($media['external']) && $media['external'] == 0) 
			{
				$dl = '<a href="'.$src.'">'.$media['title'].'</a> ('.$media['size_formatted'].')';
				$dl_text = sprintf($cms_labels['download_file'],$dl);
				echo "<p>{$dl_text}</p>\r";
			}

			// start file nav links
			$files = array();
			$navlinks = array();
			if(isset($attachments))
			{
				$files = array_merge($files,$attachments);
			}
	
			if(isset($content['dir_listing']))
			{
				$files = array_merge($files,$content['dir_listing']['files']);
			}
			
			if(count($files) > 0)
			{
				$indexes = array_keys($files);
				$myindex = array_search($media['file_uri'],$indexes);

				$prev_index = $myindex - 1;
				if(array_key_exists($prev_index,$indexes))
				{
					$key_prev = $indexes[$prev_index];
					$prev_uri = $resource_uri.'/view'.$files[$key_prev]['file_uri'];
					$navlinks[] = anchor($prev_uri,$cms_labels['result_prev'],array('title' => $files[$key_prev]['title']));
				}

				$navlinks[] = anchor($resource_uri,$cms_labels['result_index'],array('title' => $content['title']));

				$next_index = $myindex + 1;
				if(array_key_exists($next_index,$indexes))
				{
					$key_next = $indexes[$next_index];
					$next_uri = $resource_uri.'/view'.$files[$key_next]['file_uri'];
					$navlinks[] = anchor($next_uri,$cms_labels['result_next'],array('title' => $files[$key_next]['title']));
				}
				
			}
			$nav = implode(' | ',$navlinks);
			echo "<div class=\"block align-center\">{$nav}</p>\r"
			// end file nav links
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
