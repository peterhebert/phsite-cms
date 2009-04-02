<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Login | phSite</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/assets/css/base.css" rel="stylesheet" type="text/css" />
<link href="/assets/css/simple.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb"><?php echo anchor("cms","phSite CMS"); ?> : Login</div>
	<div class="page-wrap block">
		<h2 class="first">Login</h2>
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
			<p><label for="username">Username</label> 
			<input name="username" id="username" type="text" />
			</p>
			<p><label for="password">Password</label> 
			<input name="password" id="password" type="password" />
			</p>
			<p><input name="submit" type="submit" value="Submit" /></p>
			<p><?php echo anchor("cms/forgot_password","Forgot password?"); ?></p>
		</form>
		<?php } ?>
	</div>
	<div class="page-wrap block" id="footer"><?php require_once('footer.php'); ?></div>
</div>
</body>
</html>
