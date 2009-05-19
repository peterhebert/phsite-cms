<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Site preferences | phSite</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/assets/css/base.css" rel="stylesheet" type="text/css" />
<link href="/assets/css/simple.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb">
	<?php echo anchor("cms","phSite CMS"); ?> : 
	<?php echo anchor("cms/setup","Setup"); ?> : 
	Site preferences
	</div>
	<div class="page-wrap block">

			<h1 class="first">Site preferences</h1>
			<?php 
			if (isset($msg)){
			echo "<div class=\"msg\">{$msg}</div>\n";
			}
			echo form_open($action, $attributes, $hidden);
			?>
			<p><?php
			echo form_label('Home Page', 'home_page_id');
			echo form_dropdown('home_page_id', $homeArray, $home_page_id);		
			?></p>
			<p><?php
			echo form_label('Theme', 'theme');
			echo form_dropdown('theme', $themeArray, $theme);		
			?></p>
			<p><?php
			echo form_label('Default language', 'default_lang');
			echo form_dropdown('default_lang', $langArray, $default_lang);		
			?></p>
			<p><?php
			echo form_label('Webmaster', 'webmaster_id');
			echo form_dropdown('webmaster_id', $userArray, $webmaster_id);		
			?></p>

			<?php
			$field_params = array(
			'class'        => 'tiny_input align-right',
			'name'        => 'limit_posts',
			'id'          => 'limit_posts',
			'value'       => $limit_posts
			);
			echo"<p>";
			echo form_label('Posts per page (blogs)', 'limit_posts');
			echo form_input($field_params);
			echo"</p>\n";

			$field_params = array(
			'class'        => 'tiny_input align-right',
			'name'        => 'limit_thumbs',
			'id'          => 'limit_thumbs',
			'value'       => $limit_thumbs
			);
			echo"<p>";
			echo form_label('Thumbnails per page (galleries)', 'limit_thumbs');
			echo form_input($field_params);
			echo"</p>\n";

			$field_params = array(
			'class'        => 'tiny_input align-right',
			'name'        => 'img_size_thumb',
			'id'          => 'img_size_thumb',
			'value'       => $img_size_thumb
			);
			echo"<p>";
			echo form_label('Image thumbnail size', 'img_size_thumb');
			echo form_input($field_params);
			echo"</p>\n";

			$field_params = array(
			'class'        => 'tiny_input align-right',
			'name'        => 'img_size_display',
			'id'          => 'img_size_display',
			'value'       => $img_size_display
			);
			echo"<p>";
			echo form_label('Image display size', 'img_size_display');
			echo form_input($field_params);
			echo"</p>\n";
			?>

			<input name="submit" type="submit" id="submit" value="save" />
		</form>
	</div>
	<div class="page-wrap block" id="footer"><?php require_once('footer.php'); ?></div>
</div>
</body>
</html>
