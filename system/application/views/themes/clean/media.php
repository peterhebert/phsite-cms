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
<body>
<?php require_once('masthead.php'); ?>
<!-- start page wrapper -->
<div class="wrap pad-h">
	<div id="container">
		<?php
			//echo "<pre>";
//			print_r($content);
//			echo "</pre>\r";

			if(isset($msg))
			{
				echo "<div class=\"msg\">{$msg}</div>\r";
			}

			if(isset($content['title']))
			{
				echo "<h1 class=\"align-center\">{$content['title']}</h1>\r";
			}
			if(isset($content['subtitle']) && $content['subtitle'] != '')
			{
				echo "<h2>{$content['subtitle']}</h2>\r";
			}

			if(isset($content['mime_cat']))
			{
				switch($content['mime_cat'])
				{
				case 'image':
					echo "<div class=\"image image-center\" style=\"width: {$this->Site_model->img_size_lg}px\">";
					echo view_image($content['server_path'],$this->Site_model->img_size_lg);
					echo "</div>\r";
					break;
				case 'audio':
				case 'video':
					$params = array(
					'src' => $content['rel_path']
					);
					if($content['external'] == 1)
					{
						$params['src'] = $content['file_uri'];
					}
					if(in_array($content['mime_type'],$playable_types))
					{
						$media = media_player_object($params);
						echo "<div class=\"image image-center\">";
						echo $media;
						echo "</div>\r";
					}
					break;
				default:
					echo "link to file";
					break;
				}
			}
			
			if(isset($content['description']))
			{
				echo "<div class=\"block\">{$content['description']}</div>\r";
			}
			if(isset($content['creator'])) 
			{
				echo '<p>'.sprintf($cms_labels['media_credit'],$content['creator'])."</p>\r";
			}
			if(isset($content['keyword_links'])) 
			{
				echo "<p>{$cms_labels['keywords']} : {$content['keyword_links']}</p>\r";
			}
		?>
	</div>
</div>
<!-- end page wrapper -->
<!-- start footer -->
<div class="wrap" id="footer">
<?php
if (isset($site['copyright']) ) {
echo "\t<p>".$site['copyright']."</p>\r";
}
echo $cms_labels['powered_by'];
?>
</div>
<!-- end footer -->
</body>
</html>
