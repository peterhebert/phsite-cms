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
-->
</style>
</head>

<body>
	<h1>Object editor</h1>
	<?php echo $msg;
	echo form_open($action, $attributes, $hidden);
	
	 ?>
		<p><label for="name" class="block">object name</label><input name="name" type="text" id="name" value="<?php if(isset($name)) {echo $name;} ?>" /></p>
	 <fieldset><legend>Fields</legend>
		<?php foreach ($fields as $index => $vals) {
			if($vals['exists']) {
				echo form_hidden('rel_id['.$index.']', $vals['rel_id']);
			}
		

			$params = array(
			'name'        => 'fields[]',
			'id'          => 'fields[]',
			'value'       => $index,
			'checked'     => $vals['exists']
			);
	
			echo "<p><label class=\"block\">{$vals['name']}</label> <label>".form_checkbox($params)." avaiable?</label>";
	
			$params = array(
			'name'        => 'required[]',
			'id'          => 'required[]',
			'value'       => $index,
			'checked'     => $vals['required']
			);
			echo " <label>".form_checkbox($params)." required?</label></p>\n";
		
		} ?>
	 </fieldset>

		<input name="submit" type="submit" id="submit" value="save" />
	</form>


</body>
</html>
