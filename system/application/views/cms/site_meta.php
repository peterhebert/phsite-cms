<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Site metadata | phSite</title>
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
	Site metadata
	</div>
	<div class="page-wrap block">

			<h1 class="first">Site metadata</h1>
			<?php 
			if (isset($msg)){
			echo "<div class=\"msg\">{$msg}</div>\n";
			}
			echo form_open($action, $attributes);

			if ($site_meta_id != ''){
				echo form_hidden('site_meta_id', $site_meta_id);
			}
?>
			<p><?php
			echo form_label('Language', 'site_lang');
			echo form_dropdown('site_lang', $langArray, $site_lang);		
			?></p>
<?php
			$field_params = array(
			'class'        => 'text',
			'name'        => 'title',
			'id'          => 'title',
			'value'       => $title
			);
			echo"<p>";
			echo form_label('Title', 'title');
			echo form_input($field_params);
			echo"</p>\n";
		
			$field_params = array(
			'class'        => 'text',
			'name'        => 'subtitle',
			'id'          => 'subtitle',
			'value'       => $subtitle
			);
			echo"<p>";
			echo form_label('Subtitle', 'subtitle');
			echo form_input($field_params);
			echo"</p>\n";

			$field_params = array(
			'class'        => 'text',
			'name'        => 'creator',
			'id'          => 'creator',
			'value'       => $creator
			);
			echo"<p>";
			echo form_label('Creator (person or organisation)', 'creator');
			echo form_input($field_params);
			echo"</p>\n";
		
			$field_params = array(
			'class'        => 'textarea',
			'name'        => 'description',
			'id'          => 'description',
			'value'       => $description
			);
			echo"<p>";
			echo form_label('Description', 'description');
			echo form_textarea($field_params);
			echo"</p>\n";

			$field_params = array(
			'class'        => 'text',
			'name'        => 'keywords',
			'id'          => 'keywords',
			'value'       => $keywords
			);
			echo"<p>";
			echo form_label('Keywords', 'keywords');
			echo form_input($field_params);
			echo"</p>\n";

			$field_params = array(
			'class'        => 'text',
			'name'        => 'copyright',
			'id'          => 'copyright',
			'value'       => $copyright
			);
			echo"<p>";
			echo form_label('Copyright', 'copyright');
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
