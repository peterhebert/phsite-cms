<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Forgot password | phSite</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/assets/css/base.css" rel="stylesheet" type="text/css" />
<link href="/assets/css/simple.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb"><?php echo anchor("cms","phSite CMS"); ?> : User editor</div>
	<div class="page-wrap block">

			<h1 class="first">Forgot password</h1>
			<?php 
			if (isset($msg)){
			echo "<div class=\"msg\">{$msg}</div>\n";
			}

			$attributes = array('id' => 'forgot_pwd');
			echo form_open($action, $attributes, $hidden);
			
			?>
	
			<p><?php
			echo form_label('Username', 'username');
			$params = array(
			'name'        => 'username',
			'id'          => 'username',
			'value'       => $username,
			);
			echo form_input($params);
			?></p>

			<p><?php
			echo form_label('E-mail', 'email');
			$params = array(
			'name'        => 'email',
			'id'          => 'email',
			'value'       => $email,
			);
			echo form_input($params);
			?></p>

			<input name="submit" type="submit" id="submit" value="save" />
		</form>
	</div>
	<div class="page-wrap block" id="footer"><?php require_once('footer.php'); ?></div>
</div>
</body>
</html>
