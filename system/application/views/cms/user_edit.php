<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>User editor | phSite</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="<?php echo base_url(); ?>assets/css/base.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>assets/css/simple.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/tiny_mce/tiny_mce_gzip.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/tinymce_richtext.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-1.3.1.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery_external_links.js"></script>
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb">
	<?php
		if (isset($breadcrumb)){
			echo $breadcrumb;
		}
	 ?>
	</div>
	<div class="page-wrap block">

		<h1 class="first">User editor</h1>
		<?php 
		if (isset($msg)){
			echo "<div class=\"msg\">{$msg}</div>\n";
		}

		if (isset($form)){
			echo $form;
		}
		?>
	</div>
	<div class="page-wrap block" id="footer"><?php require_once('footer.php'); ?></div>
</div>
</body>
</html>
