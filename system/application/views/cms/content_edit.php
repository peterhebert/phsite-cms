<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Content editor | phSite</title>
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
<div class="page-wrap block" id="breadcrumb"><a href="#">phSite CMS</a> : Content editor</div>
<div class="page-wrap block" id="page">
	<div class="column w4">
		<h1>Content editor</h1>
		<?php
		
		$hidden = array();
		if($content_id != '') {
			$hidden['content_id'] = $content_id;
		}
		$attributes = array('id' => 'content_editor');
		
		echo form_open(site_url("cms/content/edit/".$content_id), $attributes, $hidden);

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

		foreach($content_data as $data) {
			$values[$data['field_id']] = $data['value'];
			$hiddenIDs[$data['field_id']] = $data['data_id'];
		}

/*		foreach($fields as $row) {
			echo "<p>";
			echo form_label($row['field_name']);
	
			$field = array(
			'name'        => "field[{$row['field_id']}]",
			'id'          => "field[{$row['field_id']}]",
			);
	
			if(array_key_exists($row['field_id'], $hiddenIDs)) {
				echo form_hidden('data_id[]', $hiddenIDs[$row['field_id']] );
			}

			if(array_key_exists($row['field_id'], $values)) {
				$field['value'] = form_prep($values[$row['field_id']] );
			}
			
			switch($row['input']) {
			case 'text':
				echo form_input($field);
				break;
			case 'textarea':
				$field['rows'] = 5;
				$field['cols'] = 40;
				echo form_textarea($field);
				break;
			case 'richtext':
				$field['rows'] = 10;
				$field['cols'] = 40;
				echo form_textarea($field);
				break;
			default:
				echo form_input($field);
			}
			
			echo "</p>\n";
	
		}
*/		
		echo "<p>";
		echo form_submit('submit', 'Save');
		echo "</p>\n";

		
		?>
		</form>
	</div>
	<div class="float w1">
<?php
if(is_array($otherlangs)) {
	echo "<h2>Other Languages</h2>\n";
	echo ul($otherlangs);
}
?>

	</div>
	
</div>
<div class="page-wrap block" id="footer"> phSite &copy; 2009 Peter Hebert. All rights reserved unless stated otherwise.</div>
</body>
</html>
