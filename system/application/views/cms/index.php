<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>CMS Home | phSite</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/assets/css/base.css" rel="stylesheet" type="text/css" />
<link href="/assets/css/simple.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/assets/js/jquery-1.3.1.min.js"></script>
<script type="text/javascript" src="/assets/js/jquery_external_links.js"></script>
<script type="text/javascript" src="/assets/js/jquery_new_content.js"></script>
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb"><?php echo anchor("cms","phSite CMS"); ?> : CMS Home</div>
	<div class="page-wrap block">
		<div class="column w4">
			<h1>Content</h1>
			<?php
			echo "<p>".anchor("cms/content/site_map","Site Map")."</p>\r";
			echo $content; 
		
			?>
			<p>Create New <?php echo form_dropdown('new_content',$dropdown); ?> <input id="new_go" name="new_go" type="button" value="Go" /></p>
			<h2>Media</h2>
			<ul>
				<li><?php echo anchor("cms/media/manage","manage media"); ?></li>
				<li><?php echo anchor("cms/media/upload","upload media"); ?></li>
			</ul>
		</div>
		<div class="float w2">
		<?php require_once('setup_menu.php'); ?>
		</div>
	</div>
	<div class="page-wrap block" id="footer"><?php require_once('footer.php'); ?></div>
</div>
</body>
</html>
