<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Content editor | phSite</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/css/base.css" rel="stylesheet" type="text/css" />
<link href="/css/simple.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery-1.3.1.min.js"></script>
<script type="text/javascript">
/* <![CDATA[ */
$().ready(function(){

	$("form select[name='object_id']").change(function () {
	
		var myloc = $("form input[name='my_url']").val();
		var myval = $(this).val();
		var newloc = myloc + '/type/' + myval;
		window.location = newloc ;
		
	});

});
/* ]]> */
</script>
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb"><?php echo anchor("cms","phSite CMS"); ?> : Content editor</div>
	<div class="page-wrap block">
		<h1>Content editor</h1>
		<?php
		if(isset($msg)) {
			echo"<div class=\"msg\">";
			echo $msg;
			echo"</div>\n";
		}

		// open form
		echo form_open($action);

		echo form_hidden('content_id', $content_id);
		echo form_hidden('title_id', $title_id);
		echo form_hidden('my_url', $action);

		// do main content fields
?>
<div class="column w3"><fieldset><legend>Content</legend>
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
		
		foreach($fields as $fd) {
			$field_params = array();

			$field_params['name'] = "value[]";
			$field_params['id'] = "value[]";

			$value = '';
			$data_id = '';

			if(is_array($content_data) && count($content_data) > 0) {
				foreach($content_data as $dt) {
					if($dt['field_id'] == $fd['field_id']) {
						$value = $dt['value'];
						$data_id = $dt['data_id'];
						$required = $dt['required'];
						$valid = $dt['validation'];
					}
				}
			}
			if($value != '') {
				$field_params['value'] = $value;
			}

			echo form_hidden('field_id[]', $fd['field_id']);
			echo form_hidden('required[]', $fd['required']);
			echo form_hidden('valid[]', $fd['validation']);
			echo form_hidden('data_id[]', $data_id);

			echo "<p>";
			echo form_label($fd['field_name']);


			switch($fd['input_type']) {
			case 'text':
				$field_params['class'] = $fd['input_type'];
				echo form_input($field_params);
				break;
			case 'textarea':
				$field_params['class'] = $fd['input_type'];
				$field_params['cols'] = 40;
				echo form_textarea($field_params);
				break;
			case 'richtext':
				$field_params['class'] = $fd['input_type'];
				$field_params['cols'] = 40;
				echo form_textarea($field_params);
				break;
			default:
				echo form_input($field_params);
			}
			echo "</p>\n";
						
		}
?></fieldset></div>
<div class="float w2"><fieldset><legend>Meta</legend>
<?php 
		echo"<p>";
		echo form_label('Author', 'user_id');
		echo form_dropdown('user_id', $userArray, $user_id);
		echo"</p>\n";

		echo"<p>";
		echo form_label('Content type', 'object_id');
		echo form_dropdown('object_id', $objArray, $object_id);
		echo"</p>\n";
	
		echo"<p>";
		echo form_label('Parent', 'parent_id');
		echo form_dropdown('parent_id', $parentArray, $parent_id);
		echo"</p>\n";
	
		$field_params = array(
		'name'        => 'created',
		'id'          => 'created',
		'value'       => $created
		);
		echo"<p>";
		echo form_label('Created', 'created');
		echo form_input($field_params);
		echo"</p>\n";

		echo"<p>";
		echo form_label('Language', 'lang');
		echo form_dropdown('lang', $langArray, $lang);
		echo"</p>\n";
?></fieldset></div>
<?php


		// close form
		echo "<p>";
		echo form_submit('submit', 'Save');
		echo "</p>\n";

		echo form_close();
		?>
	</div>
	<div class="page-wrap block" id="footer"><?php require_once('footer.php'); ?></div>
</div>
</body>
</html>
