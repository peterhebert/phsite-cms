<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Field editor</title>
</head>

<body>
	<h1>Field editor</h1>
	<?php echo $msg; ?>
	<form action="<?php echo $this->config->site_url("field/edit/".$this->Field_model->field_id);?>" method="post" name="editor" id="editor">
		<input name="field_id" type="hidden" id="field_id" value="<?php echo $field_id ?>" />
		<input name="name" type="text" id="name" value="<?php echo $name ?>" />
		<input name="submit" type="submit" id="submit" value="save" />
	</form>

</body>
</html>
