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
		<div class="column w4">
			<h1 class="first">Object editor</h1>
			<?php 
			if ($msg != ''){
				echo "<div class=\"msg\">{$msg}</div>";
			}
			echo form_open($action, $attributes, $hidden);
		?>
			<p>
				<label for="name" class="block">object name</label>
				<input name="name" type="text" id="name" value="<?php if(isset($name)) {echo $name;} ?>" />
			</p>
			<fieldset>
			<legend>Available Fields</legend>
			<div class="row">
				<div class="field_name">field name</div>
				<div class="in_object">in object?</div>
				<div class="required">required?</div>
				<div class="order">order</div>
			</div>
			<?php foreach ($fields as $index => $vals) {

			echo "<div class=\"row\">\n";

			echo form_hidden('fields[]', $index);

			if($vals['data_id'] != '') {
				$exists = true;
			} else {
				$exists = false;
			}
			echo form_hidden('data_id[]', $vals['data_id']);

			$params = array(
			'name'        => 'exists[]',
			'id'          => 'exists[]',
			'value'       => $index,
			'checked'     => $exists
			);
	
			echo "<label class=\"field_name\">{$vals['field_name']}</label>";
			echo "<label class=\"in_object\">".form_checkbox($params)." <span>in object?</span></label>";
			
			$params = array(
			'name'        => 'required[]',
			'id'          => 'required[]',
			'value'       => $index,
			'checked'     => $vals['required']
			);

			echo "<label class=\"required\">".form_checkbox($params)." <span>required?</span></label>";

			$params = array(
			'name'        => "order[]",
			'id'          => "order[]",
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
		</div>
		<div class="float w1"> </div>
	</div>
	<div class="page-wrap block" id="footer">phSite &copy; 2009 Peter Hebert. All rights reserved unless stated otherwise.</div>
</div>
</body>
</html>
