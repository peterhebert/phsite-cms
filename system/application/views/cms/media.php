<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Media | phSite</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/assets/css/base.css" rel="stylesheet" type="text/css" />
<link href="/assets/css/simple.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb"><?php echo anchor("cms","phSite CMS"); ?> : Media</div>
	<div class="page-wrap block">
		<h1>Media</h1>
		
		<?php
		foreach($files as $dir => $filelist) {
			if(is_array($filelist) && count($filelist) > 0) {
				$dir_rel = str_replace($media_root,'',$dir);
				echo "<h2>$dir_rel</h2>\n";
				foreach($filelist as $file) {
					$file_link = str_replace($_SERVER["DOCUMENT_ROOT"],'',$file);
					$files_clean[] = '<a href="'.$file_link.'">'.basename($file).'</a>';
				}
				echo ul($files_clean);
				unset($files_clean);
			}
		}
		?>
		<p><?php echo anchor("cms/media/upload","upload media"); ?></p>
	</div>
	<div class="page-wrap block" id="footer"><?php require_once('footer.php'); ?></div>
</div>
</body>
</html>
