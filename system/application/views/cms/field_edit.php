<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Field editor | phSite CMS</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/css/base.css" rel="stylesheet" type="text/css" />
<link href="/css/simple.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb"><?php echo anchor("cms","phSite CMS"); ?> : <?php echo anchor("cms/setup","Setup"); ?> : Field editor</div>
	<div class="page-wrap block">
		<h1 class="first">Field editor</h1>
		<?php 
		if (isset($msg)) {
			echo "<div class=\"msg\">$msg</div>\n"; 
		}
		$action = "cms/field/edit/".$field_id;
		$attributes = array('name' => "field_editor", 'id' => "field_editor");
		$hidden = array('field_id' => "$field_id");

		echo form_open($action,$attributes,$hidden);
		echo "\n";
		?>
			<p>
				<label for="field_name">Field name</label>
				<?php echo form_input('field_name',$field_name); ?></p>
			<p>
				<label for="input_type">Input type</label>
				<?php echo form_dropdown('input_type', $types, $input_type); ?></p>
			<p>
				<input name="submit" type="submit" id="submit" value="save" />
			</p>
		</form>
	</div>
	<div class="page-wrap block" id="footer"><?php require_once('footer.php'); ?></div>
</div>
</body>
</html>
