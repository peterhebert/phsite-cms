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
	<div class="page-wrap block" id="breadcrumb"><?php echo anchor("cms","phSite CMS"); ?> : Content editor</div>
	<div class="page-wrap block">
		<div class="column w4">
			<h1>Content editor</h1>
			<?php
			$hidden = array();
			$form = array();

			if($content_id != '') {
				$hidden['content_id'] = $content_id;
			}
			$attributes = array('id' => 'content_editor');
			
			echo form_open(site_url("cms/content/edit/id/".$content_id), $attributes, $hidden);
			
			if($content_id != '') {
				echo form_hidden('my_url', site_url("cms/content/edit/id/".$content_id));
			} else {
				echo form_hidden('my_url', site_url("cms/content/edit/"));
			}
	
			echo "<p>";
			echo form_label('Content type', 'parent_id');
			echo form_dropdown('object_id', $objects, $object_id);
			echo "</p>\n";
	
			$field = array(
			'name'        => 'parent_id',
			'id'          => 'parent_id',
			'value'       => $parent_id
			);
			echo "<p>";
			echo form_label('Parent', 'parent_id');
			echo form_input($field);
			echo "</p>\n";
	
			$field = array(
			'name'        => 'created',
			'id'          => 'created',
			'value'       => $created
			);
			echo "<p>";
			echo form_label('Created', 'created');
			echo form_input($field);
			echo "</p>\n";
	
			// get object fields
			foreach($fields as $row) {
				$form[$row['field_id']] = array(
					'value' => '',
					'input_type' => $row['input_type'],
					'field_name' => $row['field_name'],
					'data_id' => '',
				);
			}
			
			// get current values
			foreach($content_data as $row) {
				$form[$row['field_id']] = array(
					'value' => $row['value'],
					'input_type' => $row['input_type'],
					'field_name' => $row['field_name'],
					'data_id' => $row['data_id'],
				);
			}
	
			// generate form fields
			foreach($form as $key => $row) {
				echo "<p>";
				echo form_label($row['field_name']);
		
				$field = array(
				'name'        => "field[{$key}]",
				'id'          => "field[{$key}]",
				);
		
				if($row['data_id'] != '') {
					echo form_hidden('data_id[]', $row['data_id']);
				}
	
				if($row['value'] != '') {
					$field['value'] = form_prep($row['value']);
				}
				
				switch($row['input_type']) {
				case 'text':
					$field['class'] = 'cms_input';
					echo form_input($field);
					break;
				case 'textarea':
					$field['cols'] = 40;
					$field['class'] = 'cms_textarea';
					echo form_textarea($field);
					break;
				case 'richtext':
					$field['cols'] = 40;
					$field['class'] = 'cms_rte';
					echo form_textarea($field);
					break;
				default:
					echo form_input($field);
				}
				
				echo "</p>\n";
			}

			echo "<p>";
			echo form_submit('submit', 'Save');
			echo "</p>\n";
			?>
			</form>
		</div>
		<div class="float w1">
			<?php
			if(is_array($otherlangs) && count($otherlangs) > 0) {
				echo "<h4 class=\"first\">Other Languages</h4>\n";
				echo ul($otherlangs);
			}
			?>
		</div>
	</div>
	<div class="page-wrap block" id="footer"> phSite &copy; 2009 Peter Hebert. All rights reserved unless stated otherwise.</div>
</div>
</body>
</html>
