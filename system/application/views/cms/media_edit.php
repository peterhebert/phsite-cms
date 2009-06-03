<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Media editor | phSite</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/assets/css/base.css" rel="stylesheet" type="text/css" />
<link href="/assets/css/simple.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb"><?php echo anchor("cms","phSite CMS"); ?> : Media editor</div>
	<div class="page-wrap block">
		<h1>Media editor</h1>
		<?php
		if(isset($thumb)) {
			echo $thumb;
		}
		if(isset($form)) {
			echo $form;
		}
		
		if(isset($meta)) {
			echo "<pre>";
			print_r($meta);
			echo "</pre>";
		}
		?>

	</div>
	<div class="page-wrap block" id="footer"><?php require_once('footer.php'); ?></div>
</div>
</body>
</html>
