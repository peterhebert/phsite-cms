<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php
if (isset($html_header)) {
	echo($html_header);
}
?>
<script type="text/javascript" src="/assets/js/jquery-1.3.1.min.js"></script>
<script type="text/javascript" src="/assets/js/jquery_external_links.js"></script>
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
<?php
if(isset($breadcrumb_links))
{
	$breadcrumb = implode(' : ', $breadcrumb_links);
	echo "\t<div class=\"page-wrap block\" id=\"breadcrumb\">{$breadcrumb}</div>\r";
}
?>
	<div class="page-wrap block five">
		<div class="column w3">
			<?php
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

			if(isset($attachments) && count($attachments) > 0)
			{
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



					if(in_array($att['ext'],$mime_icons))
					{
						echo "<span class=\"file ext_{$att['ext']}\">";
					}
					else if($mime_cat != 'application')
					{
						echo "<span class=\"icon icon_{$mime_cat}\">";
					}
					else
					{
						echo "<span class=\"file\">";
					}







					echo anchor($orig_path, $filename, array('title' => sprintf($this->cms_labels['edit_this'],$filename)));
					echo "</span><br />\r";

					if(array_key_exists($att['ext'],$mime_labels))
					{
						echo $mime_labels[$att['ext']];
					}
					else
					{
						echo $att['mime_type'];
					}

					echo "</p>\r";
/*


					Array
					(
						[0] => Array
								(
									[a_id] => 2
									[content_id] => 2
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
											
					)
					*/

				}
			}

			?>
		</div>
		<div class="float w2">
			<?php

				if(isset($menu))
				{
					echo "<h4>{$menu_title}</h4>\r";
					echo build_menu($menu,2);
				}
			
			?>
		</div>
	</div>
	<div class="page-wrap block" id="footer"><?php 
	if (isset($content['copyright'])) {
		echo "<p>{$content['copyright']}</p>\r";
	} else {
		echo "<p>{$site['copyright']}</p>\r";
	}
	echo $cms_labels['powered_by'];
	?></div>
</div>
</body>
</html>