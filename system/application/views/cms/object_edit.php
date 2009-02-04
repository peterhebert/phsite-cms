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
<script type="text/javascript" src="/phsite_ci/js/jquery-1.3.1.min.js"></script>
<script type="text/javascript">
/* <![CDATA[ */
$().ready(function() {
	$('#add').click(function() {
		return !$('#select1 option:selected').remove().appendTo('#select2');
	});
	$('#remove').click(function() {
		return !$('#select2 option:selected').remove().appendTo('#select1');
	});
});

/* ]]> */
</script>
</head>
<body>
<h1>Object editor</h1>

<h2>Test of multi-select</h2>
 <div style="float:left">
  <select multiple id="select1">
   <option value="1">Option 1</option>
   <option value="2">Option 2</option>
   <option value="3">Option 3</option>
   <option value="4">Option 4</option>
  </select>
  <a href="#" id="add">add &gt;&gt;</a>
 </div>
 <div style="float:left">
  <select multiple id="select2"></select>
  <a href="#" id="remove">&lt;&lt; remove</a>
 </div>

<br clear="all" />
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
		<th scope="col" id="th-avail">available?</th>
		<th scope="col" id="th-req">required?</th>
		<th scope="col" id="th-order">order</th>
		<th scope="col">&nbsp;</th>
	</tr>
	<?php foreach ($fields as $index => $vals) {
		
		echo "<tr>\n";

//
//<td></td>
//<td>&nbsp;</td>
//<td>&nbsp;</td>
//<td>&nbsp;</td>
//</tr>



			if($vals['exists']) {
				echo form_hidden('rel_id['.$index.']', $vals['rel_id']);
			}
		

			$params = array(
			'name'        => 'fields[]',
			'id'          => 'fields[]',
			'value'       => $index,
			'checked'     => $vals['exists']
			);
	
			echo "<p><label class=\"block\">{$vals['name']}</label> <label>".form_checkbox($params)." available?</label>";
	
			$params = array(
			'name'        => 'required[]',
			'id'          => 'required[]',
			'value'       => $index,
			'checked'     => $vals['required']
			);
			echo " <label>".form_checkbox($params)." required?</label>";


			$params = array(
			'name'        => "order[{$index}]",
			'id'          => "order[{$index}]",
			'value'       => $vals['order'],
			'maxlength'   => '3',
			'size'        => '3'
			);

			echo form_label('Order', 'order');
			echo form_input($params);
			
		echo "</tr>\n";
		
		} ?>
</table>
</fieldset>
<input name="submit" type="submit" id="submit" value="save" />
</form>
</body>
</html>
