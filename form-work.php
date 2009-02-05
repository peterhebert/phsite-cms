<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Test form</title>
<style type="text/css">
<!--
#object-editor #field-list {
	margin-bottom: 1em;
	clear:both;
}
-->
</style>
<script type="text/javascript" src="js/jquery-1.3.1.min.js"></script>
<script type="text/javascript">
/* <![CDATA[ */
$().ready(function() {

	$('#add').click(function() {

		// get the value(s) from a dropdown select
		var selectedValues = $('select#available option:selected').val() || [];
		var selectedText = $.map($('#available :selected'), function(e) { return $(e).text(); });

		$('select#available option:selected').remove();
		for (var i = 0; i < selectedText.length; i++) {
			// Do something with selectedValues[i]
			$("div#field-list").append('<div class="field" id="rel-'+selectedValues[i]+'"><input type="hidden" name="field_id[]" id="field_id[]" value="'+selectedValues[i]+'" /><strong>'+selectedText[i]+'</strong><label><input type="checkbox" name="required[]" id="required[]" value="'+selectedValues[i]+'" /> required?</label><button class="remove" value="'+selectedValues[i]+'">Remove</button></div>');

		}

	});

	$('#field-list .field button.remove').click(function() {

		// get the attributes and append to list
		var fieldID = $(this).val();
		var titleText = $(this).siblings("strong").text();

		$("#available").append("<option value=\""+fieldID+"\">"+titleText+"</option>");


		// get the parent element and remove it
		$(this).parent().remove();

	});

});

/* ]]> */
</script>
</head>
<body>
<form action="" method="post" name="object-editor" id="object-editor">
	<p>
		<label for="fields">Available fields</label>
		<select name="available" multiple="multiple" id="available">
			<option value="2">Subtitle</option>
			<option value="3">Body Text</option>
			<option value="4">Intro Text</option>
			<option value="5">Description</option>
			<option value="6">Tags</option>
		</select>
		<input name="add" type="button" id="add" value="Add" />
	</p>
	<h2>Current fields assigned to object</h2>
	<div id="field-list" >
		<div class="field" id="rel-1">
			<input type="hidden" name="field_id[]" id="field_id[]" value="1" />
			<strong>Title</strong>
			<label><input type="checkbox" name="required[]" id="required[]" value="1" /> required?</label>
			<button class="remove" value="1">Remove</button>
		</div>
	</div>
</form>
</body>
</html>






