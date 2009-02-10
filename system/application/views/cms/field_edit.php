<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Field editor</title>
<style type="text/css">
<!--
label.block {
	float: left;
	width: 10em;
	margin-right: 1em;
	font-weight:bold;
}
-->
</style>
</head>

<body>
	<h1>Field editor</h1>
	<p><?php echo anchor(site_url("cms/field"),"Back to Field list");  ?></p>
	<?php echo $msg; 
	
	$action = "cms/field/edit/".$field_id;
	?>
	<form action="<?php echo $this->config->site_url($action);?>" method="post" name="editor" id="editor">
		<?php echo form_hidden('field_id', $field_id); ?>
		<p><label for="field_name" class="block">Field name</label><?php echo form_input('field_name',$field_name); ?></p>
		<p><label for="input_type" class="block">Input type</label><?php echo form_dropdown('input_type', $types, $input_type); ?></p>
		<p><input name="submit" type="submit" id="submit" value="save" /></p>
	</form>

</body>
</html>
