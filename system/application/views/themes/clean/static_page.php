<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php
if (isset($html_header)) {
	echo($html_header);
}
?>
</head>
<body>
<?php require_once('masthead.php'); ?>
<!-- start page wrapper -->
<div class="wrap pad-h">
	<div id="container" class="wide margins">
		<div id="content" class="column"><?php
			if(isset($content['title']))
			{
				echo "<h1>{$content['title']}</h1>\r";
			}
			if(isset($content['subtitle']))
			{
				echo "<h2>{$content['subtitle']}</h2>\r";
			}
			if(isset($content['body']))
			{
				echo $content['body'];
			}


		?>
		</div>
		<div id="menu" class="float">
		<?php
		if(isset($attachments) && count($attachments) > 0)
		{

			echo "<div id=\"file-attachments\" class=\"block\">";
			echo "<h3>{$cms_labels['attachments']}</h3>\r";

			foreach($attachments as $att)
			{
				// embed media or link to file
				$orig_path = $img_path_orig.$att['file_uri'];

				// if local image, do thumbnail
				if ($att['mime_cat'] == 'image' && $att['external'] == 0)
				{
					echo "<div class=\"image-left\">";
					$thumb = view_image($orig_path,$img_size_th,array('alt' => $att['title']));
					echo $thumb;
					echo "</div>";
				}

				echo "<p>";


				if($att['title'] != '' && $att['title'] != $att['filename'])
				{
					$filename = $att['title'];
				}
				else
				{
					$filename = $att['filename'];
				}



				if(array_key_exists('extension',$att) && in_array($att['extension'],$mime_icons))
				{
					echo "<span class=\"file ext_{$att['extension']}\">";
				}
				else if($att['mime_cat'] != 'application')
				{
					echo "<span class=\"icon icon_{$att['mime_cat']}\">";
				}
				else
				{
					echo "<span class=\"file\">";
				}


				echo anchor($orig_path, $filename, array('title' => sprintf($this->cms_labels['edit_this'],$filename)));
				echo "</span><br />\r";

				if(array_key_exists('extension',$att) && array_key_exists($att['extension'],$mime_labels))
				{
					echo $mime_labels[$att['extension']];
				}
				else
				{
					echo $att['mime_type'];
				}

				if(array_key_exists('filesize_formatted',$att))
				{
					echo ' - '.$att['filesize_formatted'];
				}

				echo "</p>\r";
				echo "</div>\r";
			}
		}

		if(isset($menu) && count($menu) > 0)
		{
			$skip = NULL;
			if($content['parent_id'] > 0)
			{
				$skip = $content['content_id'];
			}
			$menutxt = trim(build_menu($menu,2,$skip));
			
			if($menutxt != '')
			{
				echo "<h4>{$menu_title}</h4>\r";
				echo $menutxt;
			}
		}
		?>
		</div>
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