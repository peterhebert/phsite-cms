<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php
if(isset($html_header))
{
echo $html_header;
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

			if(isset($parents) && count($parents) > 0)
			{
				echo "<p>parents:</p>\r";
				echo "<pre>";
				print_r($parents);
				echo "</pre>\r";
			}

			if(isset($attachments) && count($attachments) > 0)
			{
				echo "<p>attachments:</p>\r";
				echo "<pre>";
				print_r($attachments);
				echo "</pre>\r";
				foreach($attachments as $att)
				{
					// embed media or link to file
					

					/*
					Array
					(
						[0] => Array
							(
								[a_id] => 2
								[content_id] => 2
								[media_id] => 1
								[file_uri] => /phsite_0.1.zip
								[external] => 0
								[mime_type] => application/zip
								[date_created] => 2010-04-23 15:56:28
								[publish] => 1
								[title] => phsite_0.1.zip
								[uri_title] => phsite-0-1-zip
								[subtitle] => 
								[keywords] => 
							)
					
					)
					*/

					$mime_cat = preg_replace('#^([a-z]+)/(.*)$#','$1',$att['mime_type']);
//					$img_path = $img_path_orig.$att['file_uri'];
//					$thumb = view_image($img_path,$img_size_md,array('alt' => $att['title']));
					//echo $thumb;
					echo $mime_cat;
				}
			}

			?>
		</div>
		<div class="float w2">
			<?php

				if(count($parents) == 0)
				{
					$menu_title = $this->Site_model->title;
				}
				else
				{
					$menu_title = $parents[0]['title'];
				}
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
		echo "<p>{$site_meta_copyright}</p>\r";
	}
	echo $cms_labels['powered_by'];
	?></div>
</div>
</body>
</html>
