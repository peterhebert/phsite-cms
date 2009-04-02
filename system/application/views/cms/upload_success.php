<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>File uploaded successfully | phSite CMS</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/assets/css/base.css" rel="stylesheet" type="text/css" />
<link href="/assets/css/simple.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb">
	<?php echo anchor("cms","phSite CMS"); ?> : 
	<?php echo anchor("cms/media/upload","Upload a file"); ?> : 
	File uploaded successfully
	</div>
	<div class="page-wrap block">
		<h1 class="first">File uploaded successfully</h1>
		<?php
		if (isset($image_thumb))
		{
			echo "<p>$image_thumb</p>";
		}
		if (isset($upload_data))
		{
			echo "<p><strong>File name</strong>: ".$upload_data['file_name']."</p>";

			if(array_key_exists('image_type',$upload_data))
			{
				echo "<p><strong>Type</strong>: ".$upload_data['image_type']."</p>";
			}
			if(array_key_exists('image_width',$upload_data))
			{
				echo "<p><strong>Width</strong>: ".$upload_data['image_width']."</p>";
			}
			if(array_key_exists('image_width',$upload_data))
			{
				echo "<p><strong>Height</strong>: ".$upload_data['image_height']."</p>";
			}
		}
		?>
	</div>
	<div class="page-wrap block" id="footer"><?php require_once('footer.php'); ?></div>
</div>
</body>
</html>
