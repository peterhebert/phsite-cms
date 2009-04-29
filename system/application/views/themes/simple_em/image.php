<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="<?php echo $theme_path; ?>base.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $theme_path; ?>simple_em.css" rel="stylesheet" type="text/css" />
</head>
<body class="gallery">
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb"><?php echo $breadcrumb; ?></div>
	<div class="page-wrap block">
		<h1><?php echo $title; ?></h1>
		<p>Posted <?php echo $created ?> by <?php echo $author['name_first'].' '.$author['name_last']; ?></p>
		<?php echo $content_data['body']; ?>
		<p>Keywords: <?php echo $content_data['keywords']; ?>
	</div>
	<div class="page-wrap block" id="footer"><?php echo $site_meta_copyright; ?></div>
</div>
</body>
</html>
