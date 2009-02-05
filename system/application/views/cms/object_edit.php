<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Object editor</title>
<style type="text/css">
<!--
label.block {
	float: left;
	width: 10em;
	margin-right: 1em;
	font-weight:bold;
}

fieldset table td, fieldset table th {
text-align: left;
padding: 1ex 1em;
}

-->
</style>
<script type="text/javascript" src="/phsite_ci/js/jquery-1.3.1.min.js"></script>
<script type="text/javascript">
/* <![CDATA[ */
$().ready(function() {
	

	$("form :checkbox[name='required[]'").click(function() {
	
		var ischecked = $(this).attr('checked');
		var myname = $(this).attr('id');
		var myval = $(this).val();
		var parentEls = $(this).parents();
		
		if(ischecked == true and myname == 'required[]') {

		}
	});

});

/* ]]> */
</script>
</head>
<body>
<h1>Object editor</h1>

<p><?php echo anchor(site_url("cms/object"),"Back to Object list");  ?></p>
<?php echo $msg;
	
	
	
	
	echo form_open($action, $attributes, $hidden);
	
	 ?>
<p>
	<label for="name" class="block">object name</label>
	<input name="name" type="text" id="name" value="<?php if(isset($name)) {echo $name;} ?>" />
</p>
<fieldset>
<legend>Fields</legend>
<table>
	<tr>
		<th scope="col" id="th-myname">Field name</th>
		<th scope="col" id="th-avail">available?</th>
		<th scope="col" id="th-req">required?</th>
		<th scope="col" id="th-order">order</th>
	</tr>
	<?php foreach ($fields as $index => $vals) {
		
			echo "<tr>\n";

			if($vals['exists']) {
				echo form_hidden('rel_id['.$index.']', $vals['rel_id']);
			}
		

			$params = array(
			'name'        => 'fields[]',
			'id'          => 'fields[]',
			'value'       => $index,
			'checked'     => $vals['exists']
			);
	
			echo "<th id=\"name-{$index}\">{$vals['name']}</th>";
			echo "<td headers=\"name-{$index} th-avail\">".form_checkbox($params)."</td>";
			
			$params = array(
			'name'        => 'required[]',
			'id'          => 'required[]',
			'value'       => $index,
			'checked'     => $vals['required']
			);

			echo "<td headers=\"name-{$index} th-req\">".form_checkbox($params)."</td>";

			$params = array(
			'name'        => "order[{$index}]",
			'id'          => "order[{$index}]",
			'value'       => $vals['order'],
			'maxlength'   => '3',
			'size'        => '3'
			);

			echo "<td headers=\"name-{$index} th-order\">".form_input($params)."</td>";
			
			echo "</tr>\n";
		
		} ?>
</table>
</fieldset>
<input name="submit" type="submit" id="submit" value="save" />
</form>
</body>
</html>
