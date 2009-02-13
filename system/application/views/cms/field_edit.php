<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Field editor | phSite CMS</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/css/base.css" rel="stylesheet" type="text/css" />
<link href="/css/simple.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery-1.3.1.min.js"></script>

<script type="text/javascript">
/* <![CDATA[ */
$().ready(function(){

	$("#exact_length").blur(function () {
	
		if( $(this).val() != '' ) {
			$("#min_length").val("");
			$("#max_length").val("");
		}
		
	});

	$("#max_length").blur(function () {
	
		if( $(this).val() != '' ) {
			$("#exact_length").val("");
		}
		
		if( $(this).val() == $("#min_length").val() ) {
			var myval = $(this).val();
			$("#exact_length").val(myval);
			$("#min_length").val("");
			$(this).val("");
		}

		var themax = $(this).val();
		var themin = $("#min_length").val();

		var intmin = parseInt(themin);
		var intmax = parseInt(themax);
		
		var newmax = intmin +1;


		if( intmax < intmin ) {
			alert('maximum cannot be less than minimum');
			$(this).val(newmax);
		}

	});

	$("#min_length").blur(function () {

		if( $(this).val() != '' ) {
			$("#exact_length").val("");
		}

		var themin = $(this).val();
		var themax = $("#max_length").val();

		var intmin = parseInt(themin);
		var intmax = parseInt(themax);
		
		var newmin = intmax -1;

		if (newmin < 0 ) {
			newmin = 0;
		}

		if( intmin > intmax) {
			alert('minimum cannot be more than maximum');
			$(this).val(newmin);
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
			<h2><a href="/">Peter Hebert</a></h2>
		</div>
		<div class="column lh">web, film, etc...</div>
		<div class="float-right lh">
			<form action="../../../../css/search.php" method="post" id="site-search">
				<input name="search-text" id="search-text" type="text" />
				<input name="search-submit" type="submit" id="search-submit" value="Search" />
			</form>
		</div>
	</div>
</div>
<div id="page">
	<div class="page-wrap block" id="breadcrumb"><?php echo anchor("cms","phSite CMS"); ?> : Field editor</div>
	<div class="page-wrap block">
		<h1 class="first">Field editor</h1>
		<?php echo $msg; 
		$action = "cms/field/edit/".$field_id;
		?>
		<form action="<?php echo $this->config->site_url($action);?>" method="post" name="editor" id="editor">
			<?php echo form_hidden('field_id', $field_id); ?>
			<p>
				<label for="field_name">Field name</label>
				<?php echo form_input('field_name',$field_name); ?></p>
			<p>
				<label for="input_type">Input type</label>
				<?php echo form_dropdown('input_type', $types, $input_type); ?></p>
			<fieldset>
			<legend>Validation rules</legend>
			<p>
				<label>Length</label>
				<label for="exact_length" class="inline">Exact</label>
				<input type="text" name="exact_length" id="exact_length" size="3" />
				<label for="min_length" class="inline">Minimum</label>
				<input type="text" name="min_length" id="min_length" size="3" />
				<label for="max_length" class="inline">Maximum</label>
				<input type="text" name="max_length" id="max_length" size="3" />
			</p>
			<p>
				<label>Content of field is</label>
				<?php
				echo form_dropdown('string', $string_rules, $string);
				?>

			</p>
			</fieldset>
			<p>
				<input name="submit" type="submit" id="submit" value="save" />
			</p>
		</form>
	</div>
	<div class="page-wrap block" id="footer"> &copy; 1996-2009 Peter Hebert. All rights reserved unless stated otherwise.</div>
</div>
</body>
</html>
