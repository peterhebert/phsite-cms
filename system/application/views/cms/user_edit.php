<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>User editor | phSite</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/css/base.css" rel="stylesheet" type="text/css" />
<link href="/css/simple.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="masthead">
	<div class="page-wrap block">
		<div class="column">
			<h2><a href="/">phSite</a></h2>
		</div>
		<div class="column lh">a content manager for the rest of us</div>
		<div class="float-right lh">
			<form action="<?php echo site_url("cms/content/search") ?>" method="post" id="site-search">
				<input name="search-text" id="search-text" type="text" />
				<input name="search-submit" type="submit" id="search-submit" value="Search" />
			</form>
		</div>
	</div>
</div>
<div id="page">
	<div class="page-wrap block" id="breadcrumb"><?php echo anchor("cms","phSite CMS"); ?> : User editor</div>
	<div class="page-wrap block">

			<h1 class="first">User editor</h1>
			<?php 
			if ($msg != ''){
				echo "<div class=\"msg\">{$msg}</div>\n";
			}
			$attributes = array('id' => 'user_editor');
			echo form_open($action, $attributes, $hidden);
			
			$options = array(
			'small'  => 'Administrator',
			'med'    => 'User',
			'large'   => 'Large Shirt',
			'xlarge' => 'Extra Large Shirt',
			);
			
			$shirts_on_sale = array('small', 'large');
			
			echo form_dropdown('shirts', $options, 'large');		
			
			
			
		?>
			<input name="id" id="id" type="hidden" value="<?php echo $user->id; ?>" />
			<input name="admin" id="admin" type="hidden" value="1" />
			<p>* <?php echo $labels['disclaimer_userpwd']; ?></p>
			<div id="main" class="column w2">
	
					<fieldset>
						<legend>User Info</legend>
						<p>All fields are required</p>
						<p><?php
						echo form_label('First Name', 'name_first');
						$params = array(
						'name'        => 'name_first',
						'id'          => 'name_first',
						'value'       => $name_first,
						);
						echo form_input($params);
						?></p>

							<input name="" type="text" id="name_first" value="<?php echo $user->name_first ?>" />
						</div>
						<div class="block">
							<label for="name_last"><?php echo $labels['last_name']; ?></label>
							<input name="name_last" type="text" id="name_last" value="<?php echo $user->name_last; ?>" />
						</div>
						<div class="block">
							<label for="username"><?php echo $labels['user_name']; ?> * </label>
							<input name="username" id="username" type="text" value="<?php echo $user->username ?>" />
						</div>
						<div class="block">
							<label for="email"><?php echo $labels['email']; ?></label>
							<input name="email" id="email" type="text" value="<?php echo $user->email ?>" />
						</div>
						<p>
							<label for="timezone"><?php echo $labels['time_zone']; ?></label><br />
							<select name="timezone" id="timezone">
							<?php
							$timezone_identifiers = DateTimeZone::listIdentifiers();
		
							foreach ($timezone_identifiers as $zone) {
								if($zone == $user->timezone) {
									$selected = " selected=\"selected\"";
								} else {
									$selected = '';
								}
								echo "\t<option value=\"{$zone}\"{$selected}>{$zone}</option>\r";
							}
							?>
							</select>
						</p>
						<div class="block">
							<label for="lang"><?php echo $labels['default_lang']; ?></label>
							<select name="lang" id="lang">
								<?php echo getOptions('languages', $user->lang); ?>
							</select>
						</div>

				</fieldset>

				<fieldset>
					<legend>Change Password</legend>
					<?php
					if ($user->id != '') {
						$lbl_pwd = 'Choose new password';
						$lbl_conf = 'Confirm new password';
					} else {
						$lbl_pwd = 'Choose password';
						$lbl_conf = 'Confirm password';
					}
					?>
					<p>
						<label for="password_new"><?php echo $lbl_pwd; ?></label>
						<input name="password_new" id="password_new" type="password" />
					</p>
					<p>
						<label for="password_confirm"><?php echo $lbl_conf; ?></label>
						<input name="password_confirm" id="password_confirm" type="password" />
					</p>
				</fieldset>













			<input name="submit" type="submit" id="submit" value="save" />
			</form>

	</div>
	<div class="page-wrap block" id="footer">phSite &copy; 2009 Peter Hebert. All rights reserved unless stated otherwise.</div>
</div>
</body>
</html>
