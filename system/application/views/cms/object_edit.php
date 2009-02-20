<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Object editor | phSite</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/css/base.css" rel="stylesheet" type="text/css" />
<link href="/css/simple.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery-1.3.1.min.js"></script>
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
	<div class="page-wrap block" id="breadcrumb"><?php echo anchor("cms","phSite CMS"); ?> : Object editor</div>
	<div class="page-wrap block">

			<h1 class="first">Object editor</h1>
			<?php 
			if ($msg != ''){
				echo "<div class=\"msg\">{$msg}</div>\n";
			}
			echo form_open($action, $attributes, $hidden);
		?>
			<p>
				<label for="name" class="block">object name</label>
				<input name="name" type="text" id="name" value="<?php if(isset($name)) {echo $name;} ?>" />
			</p>
			<fieldset>
			<legend>Available Fields</legend>
			<table id="object_field_props">
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
	<div class="page-wrap block" id="footer">phSite &copy; 2009 Peter Hebert. All rights reserved unless stated otherwise.</div>
</div>
</body>
</html>
