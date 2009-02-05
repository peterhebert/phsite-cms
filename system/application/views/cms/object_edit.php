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

div.form-row { clear:both; }
div.form-row label, div.form-row div {
	float: left;
	margin-right: 1em;
	width: 4em;
}
div.form-row .field_name {
	width: 6em;
}
div.form-row .in_object {
}
div.form-row .required {
}
div.form-row .order {
}
label span {
	display:none;
}

-->
</style>
<script type="text/javascript" src="/phsite_ci/js/jquery-1.3.1.min.js"></script>
<script type="text/javascript">
/* <![CDATA[ */
$().ready(function() {
	

	$("form input[name='required[]']").click(function() {
	
		if( $(this).attr('checked') == true ) {
			$(this).parent().siblings(1).children(0).attr("checked",true)
		}
		
	});

	$("form input[name='fields[]']").click(function() {
	
		if( $(this).attr('checked') == false ) {
			$(this).parent().siblings(1).children(0).attr("checked",false)
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

	<div class="form-row">
		<div class="field_name">field name</div>
		<div class="in_object">in object?</div>
		<div class="required">required?</div>
		<div class="order">order</div>
	</div>
	<?php foreach ($fields as $index => $vals) {
		
			echo "<div class=\"form-row\">\n";

			if($vals['exists']) {
				echo form_hidden('rel_id['.$index.']', $vals['rel_id']);
			}
		

			$params = array(
			'name'        => 'fields[]',
			'id'          => 'fields[]',
			'value'       => $index,
			'checked'     => $vals['exists']
			);
	
			echo "<label class=\"field_name\">{$vals['name']}</label>";
			echo "<label class=\"in_object\">".form_checkbox($params)." <span>in object?</span></label>";
			
			$params = array(
			'name'        => 'required[]',
			'id'          => 'required[]',
			'value'       => $index,
			'checked'     => $vals['required']
			);

			echo "<label class=\"required\">".form_checkbox($params)." <span>required?</span></label>";

			$params = array(
			'name'        => "order[{$index}]",
			'id'          => "order[{$index}]",
			'value'       => $vals['order'],
			'maxlength'   => '3',
			'size'        => '3'
			);

			echo "<label class=\"order\"><span>order</span> ".form_input($params)."</label>";
			
			echo "</div>\n";
		
		} ?>
</fieldset>
<input name="submit" type="submit" id="submit" value="save" />
</form>
</body>
</html>
