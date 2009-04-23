<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>User editor | phSite</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/assets/css/base.css" rel="stylesheet" type="text/css" />
<link href="/assets/css/simple.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/assets/js/jquery-1.3.1.min.js"></script>
<script type="text/javascript">
/* <![CDATA[ */
$().ready(function(){

	var dateObj = new Date()
	var currentTimeZoneOffset = dateObj.getTimezoneOffset();

	//$("form select[name='timezone']").val(currentTimeZoneOffset);

});
/* ]]> */
</script>
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb">
	<?php echo anchor("cms","phSite CMS"); ?> : 
	<?php echo anchor("cms/setup","Setup"); ?> : 
	<?php echo anchor("cms/setup/users","Users"); ?> : 
	User editor
	</div>
	<div class="page-wrap block">

			<h1 class="first">User editor</h1>
			<?php 
			if (isset($msg)){
			echo "<div class=\"msg\">{$msg}</div>\n";
			}

			$attributes = array('id' => 'user_editor');
			echo form_open($action, $attributes, $hidden);
			
			?>
	
				<fieldset>
					<legend>User Info</legend>
					<p>All fields are required</p>
					<p><?php
					$options = array(0 => 'Basic User', 1  => 'Administrator');
					echo form_label('User type', 'admin');
					echo form_dropdown('admin', $options, $admin);		
					?></p>

					<p><?php
					echo form_label('First Name', 'name_first');
					$params = array(
					'name'        => 'name_first',
					'id'          => 'name_first',
					'value'       => $name_first,
					);
					echo form_input($params);
					?></p>

					<p><?php
					echo form_label('Last Name', 'name_last');
					$params = array(
					'name'        => 'name_last',
					'id'          => 'name_last',
					'value'       => $name_last,
					);
					echo form_input($params);
					?></p>

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

					<p><?php
					echo form_label('Time Zone', 'timezone');
					echo form_dropdown('timezone', $zones, $timezone);		
					?></p>

					<p><?php
					$options = array('eng' => 'English', 'fra'  => 'Fran&ccedil;ais');
					echo form_label('Default language', 'default_lang');
					echo form_dropdown('default_lang', $options, $default_lang);		
					?></p>

			</fieldset>

			<fieldset>
				<legend>Password</legend>
				<?php
				if ($user_id != '') {
					$lbl_pwd = 'Choose new password';
					$lbl_conf = 'Confirm new password';
				} else {
					$lbl_pwd = 'Choose password';
					$lbl_conf = 'Confirm password';
				}
				?>


				<p><?php
				echo form_label($lbl_pwd);
				$params = array(
				'name'        => 'password',
				'id'          => 'password',
				);
				echo form_password($params);
				?></p>

				<p><?php
				echo form_label($lbl_conf);
				$params = array(
				'name'        => 'password_confirm',
				'id'          => 'password_confirm',
				);
				echo form_password($params);
				?></p>
			</fieldset>
			<input name="submit" type="submit" id="submit" value="save" />
		</form>
	</div>
	<div class="page-wrap block" id="footer"><?php require_once('footer.php'); ?></div>
</div>
</body>
</html>
