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

		if ($att['external'] == 0)
		{
			$mylink = BASE_PATH.MEDIA_PATH_ORIG.$att['file_uri'];
		}
		else
		{
			$mylink = $att['file_uri'];
		}


		echo anchor($mylink, $filename, array('title' => sprintf($this->cms_labels['edit_this'],$filename)));
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
?>
