<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Upload a file | phSite CMS</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/css/base.css" rel="stylesheet" type="text/css" />
<link href="/css/simple.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb">
	<?php echo anchor("cms","phSite CMS"); ?> : 
	Upload a file</div>
	<div class="page-wrap block">
		<h1 class="first">Upload a file</h1>
		<?php 
		if (isset($msg)) {
			echo "<div class=\"msg\">$msg</div>\n"; 
		}
		$action = "cms/upload/do_upload";
		$attributes = array('name' => "file_uploader", 'id' => "file_uploader");

		echo form_open_multipart($action,$attributes);
		echo "\n";

		echo form_hidden('MAX_FILE_SIZE', $max_size);
		echo "\n";

		$field_params = array(
		'name'        => 'userfile',
		'id'          => 'userfile',
		);

		echo"<p>";
		echo form_label('File', 'userfile');
		echo form_upload($field_params);
		echo"</p>\n";

		$field_params = array(
		'name'        => 'dest_path',
		'id'          => 'dest_path',
		);
		echo"<p>";
		echo form_label('destination path', 'dest_path');
		echo form_dropdown('dest_path',$directories);
		echo"</p>\n";

		$field_params = array(
		'name'        => 'create_entry',
		'id'          => 'create_entry',
		'value'       => 1,
		);
		
		echo"<p>";
		echo form_label('Add to database?', 'create_entry');
		echo form_checkbox($field_params);
		echo"</p>\n";

		?>
			<p>
				<input name="submit" type="submit" id="submit" value="save" />
			</p>
		</form>
	</div>
	<div class="page-wrap block" id="footer"><?php require_once('footer.php'); ?></div>
</div>
</body>
</html>
<!--
<head>
<title>Upload Form</title>
</head>
<body>

<?php echo $error;?>


<input type="file" name="userfile" size="20" />

<br /><br />

<input type="submit" value="upload" />

</form>

</body>
</html>
-->