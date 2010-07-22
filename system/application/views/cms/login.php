<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Login | phSite</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<link href="<?php echo BASE_PATH; ?>/assets/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="<?php echo BASE_PATH; ?>/assets/css/fonts.css" rel="stylesheet" type="text/css" media="all" />
<link href="<?php echo BASE_PATH; ?>/assets/css/grid.css" rel="stylesheet" type="text/css" media="screen" />
<link href="<?php echo BASE_PATH; ?>/assets/css/admin.css" rel="stylesheet" type="text/css" media="screen" />
<link href="<?php echo BASE_PATH; ?>/assets/css/admin-mobile.css" rel="stylesheet" type="text/css" media="mobile" />

</head>
<body>
<?php require_once('masthead.php'); ?>
	<div class="wrap pad-h">
		<h2 class="first"><?php echo $cms_labels['login']; ?></h2>
		<?php
		if (isset($msg)){
		echo "<div class=\"msg\">{$msg}</div>\n";
		}

		if ($showform)
		{
		$attributes = array('id' => 'login');
		echo form_open($action, $attributes);
		echo "\n";
		?>
			<div class="align-labels label-normal">
				<p><label for="username"><?php echo $cms_labels['username']; ?></label> 
				<input name="username" id="username" type="text" />
				</p>
				<p><label for="password"><?php echo $cms_labels['password']; ?></label> 
				<input name="password" id="password" type="password" />
				</p>
			</div>
			<p class="indent-normal"><input name="submit" type="submit" value="<?php echo $cms_labels['submit']; ?>" /></p>
			<p><?php echo anchor("cms/forgot_password",$cms_labels['password_forgot']); ?></p>
		</form>
		<?php } ?>
	</div>
	<div class="wrap" id="footer"><?php require_once('footer.php'); ?></div>

</body>
</html>
