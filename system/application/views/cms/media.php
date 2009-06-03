<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Media manager | phSite</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/assets/css/base.css" rel="stylesheet" type="text/css" />
<link href="/assets/css/simple.css" rel="stylesheet" type="text/css" />
<style type="text/css">
<!--
ul.thumbnails li {
	min-height: <?php echo round(($size/15)+.5,3); ?>em;
}
ul.thumbnails li .thumb { width: <?php echo $size + 4 ; ?>px; height: <?php echo $size + 4; ?>px; }
-->
</style>
<script type="text/javascript" src="/assets/js/jquery-1.3.1.min.js"></script>
<script type="text/javascript">
/* <![CDATA[ */
$().ready(function(){

	$("form select[name='directory']").change(function () {
	
		var myval = $(this).val();
		var newloc = '<?php echo $page; ?>' + myval;
		window.location = newloc ;
		
	});

});

/* ]]> */
</script>
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb"><?php echo anchor("cms","phSite CMS"); ?> : Media manager</div>
	<div class="page-wrap block">
		<h1>Manage media</h1>
		<p><?php echo anchor("cms/media/upload","upload media"); ?></p>
		<?php
		if(isset($dirs)) {
			echo form_open('cms/media');
			echo $dirs;
			echo "<noscript>";
			echo form_submit('submit', 'Load');
			echo "</noscript>\r";
			echo form_close();
		}
		if(isset($files)) {
			echo $files;
		} ?>
	</div>
	<div class="page-wrap block" id="footer"><?php require_once('footer.php'); ?></div>
</div>
</body>
</html>
