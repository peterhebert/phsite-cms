<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Users | phSite</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="<?php echo $base_path; ?>/assets/css/base.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $base_path; ?>/assets/css/simple.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb"><?php echo anchor("cms","phSite CMS"); ?> : <?php echo anchor("cms/setup","Setup"); ?> : Users</div>
	<div class="page-wrap block">
		<h2 class="first">Users</h2>
		<?php
		if (isset($msg)){
		echo "<div class=\"msg\">{$msg}</div>\n";
		}
		if(isset($list)) {
			echo $list;
		}
		if(isset($admin) && $admin == 1) {
		echo "<p>". anchor("cms/user/edit","New User")."</p>";
		}
		?>
	</div>
	<div class="page-wrap block" id="footer"><?php require_once('footer.php'); ?></div>
</div>
</body>
</html>
