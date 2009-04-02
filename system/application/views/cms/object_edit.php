<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Object editor | phSite</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/assets/css/base.css" rel="stylesheet" type="text/css" />
<link href="/assets/css/simple.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/assets/js/jquery-1.3.1.min.js"></script>
<script type="text/javascript">
/* <![CDATA[ */
$().ready(function() {
	

	$("form input[name='required[]']").click(function() {
	
		if( $(this).attr('checked') == true ) {
			$(this).parent().siblings(1).children(0).attr("checked",true);
		}
		
	});

	$("form input[name='exists[]']").click(function() {

		if( $(this).attr('checked') == false ) {
			$(this).parent().siblings(1).children(0).attr("checked",false);

			var myIndex = $(this).parent().siblings(1).children(0).val();
			var OrderMatch = "form input[name='order["+myIndex+"]']";
			$(OrderMatch).val("");

		}

	});

});

/* ]]> */
</script>
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb">
	<?php echo anchor("cms","phSite CMS"); ?> : 
	<?php echo anchor("cms/setup","Setup"); ?> : 
	<?php echo anchor("cms/setup/structure","Data Structure"); ?> : 
	Object editor
	</div>
	<div class="page-wrap block">

			<h1 class="first">Object editor</h1>
			<?php 
			if ($msg != ''){
				echo "<div class=\"msg\">{$msg}</div>\n";
			}
			echo form_open($action, $attributes, $hidden);
		?>
			<p>
				<label for="name">object name</label>
				<input name="name" type="text" id="name" value="<?php if(isset($name)) {echo $name;} ?>" />
			</p>
			<p class="nomargin">
				<label for="name">object URI</label>
				<input name="name_uri" type="text" id="name_uri" value="<?php if(isset($name_uri)) {echo $name_uri;} ?>" />
			</p>
			<p class="indent">
				Note: changing this URI will mean you need to change the names of your template file for this object as well.
			</p>
			<fieldset>
			<legend>Available Fields</legend>
			<table id="properties">
			<thead>
				<tr>
					<th scope="col" id="hd_name">field name</th>
					<th scope="col" id="hd_exists">in object?</th>
					<th scope="col" id="hd_req">required?</th>
					<th scope="col" id="hd_order">order</th>
					<th scope="col" id="hd_val">value is</th>
					<th scope="col" id="hd_exact">Exact Length</th>
					<th scope="col" id="hd_min">Minimum Length</th>
					<th scope="col" id="hd_max">Maximum Length</th>
				</tr>
			</thead>
			<tbody>
			
			<?php
			$fieldCount = count($fields);
			$fieldNo = 1;
			foreach ($fields as $index => $vals) {
				/*echo "<tr><td colspan=\"8\"><pre>";
				print_r($vals);
				echo "</pre></td></tr>\n";*/

				if($fieldNo < $fieldCount) {
				echo "<tr>\n";
				} else {
				echo "<tr class=\"last\">\n";
				}
				$fieldNo = $fieldNo + 1;
	
				echo form_hidden('field_id[]', $index);
	
				if($vals['rel_id'] != '') {
					$exists = true;
				} else {
					$exists = false;
				}
				echo form_hidden('rel_id[]', $vals['rel_id']);
	
				$params = array(
				'name'        => 'exists[]',
				'id'          => 'exists[]',
				'value'       => $index,
				'checked'     => $exists
				);
		
				echo "<th headers=\"hd_name\" id=\"{$vals['field_name']}\" scope=\"row\">{$vals['field_name']}</th>\n";
				echo "<td headers=\"hd_exists\" class=\"align-center\">".form_checkbox($params)."</td>\n";
	
				$params = array(
				'name'        => 'required[]',
				'id'          => 'required[]',
				'value'       => $index,
				'checked'     => $vals['required']
				);
	
				echo "<td headers=\"{$vals['field_name']} hd_req\" class=\"align-center\">".form_checkbox($params)."</td>\n";
	
				$params = array(
				'name'        => "order[$index]",
				'id'          => "order[$index]",
				'value'       => $vals['order'],
				'maxlength'   => '3',
				'size'        => '3'
				);
	
				echo "<td headers=\"{$vals['field_name']} hd_order\">".form_input($params)."</td>\n";

				echo "<td headers=\"{$vals['field_name']} hd_val\">".form_dropdown("string[]", $string_rules, $vals['string'])."</td>\n";
	
				$params = array(
				'name'        => "exact_length[$index]",
				'id'          => "exact_length[$index]",
				'value'       => $vals['exact_length'],
				'maxlength'   => '3',
				'size'        => '3'
				);
				echo "<td headers=\"{$vals['field_name']} hd_exact\">".form_input($params)."</td>\n";
	
				$params = array(
				'name'        => "min_length[$index]",
				'id'          => "min_length[$index]",
				'value'       => $vals['min_length'],
				'maxlength'   => '3',
				'size'        => '3'
				);
				echo "<td headers=\"{$vals['field_name']} hd_min\">".form_input($params)."</td>\n";
	
				$params = array(
				'name'        => "max_length[$index]",
				'id'          => "max_length[$index]",
				'value'       => $vals['max_length'],
				'maxlength'   => '3',
				'size'        => '3'
				);
				echo "<td headers=\"{$vals['field_name']} hd_max\">".form_input($params)."</td>\n";
	
				echo "</tr>\n";
			
			} ?>
			</tbody></table>
			</fieldset>

			<input name="submit" type="submit" id="submit" value="save" />
			</form>

	</div>
	<div class="page-wrap block" id="footer"><?php require_once('footer.php'); ?></div>
</div>
</body>
</html>
