$().ready(function(){

	$("form#content_editor select[name='type_id']").change(function () {
	
		var myloc = $("form input[name='my_url']").val();
		var content_id = $("form input[name='content_id']").val();
		var lang = $("form select[name='content_lang']").val();
		var new_type = $(this).val();

		if(content_id != '')
		{
			myloc += '/id/' + content_id;
		}
		if(lang != '')
		{
			myloc += '/lang/' + lang;
		}
		
		myloc += '/type/' + new_type;
		window.location = myloc;
		
	});

});
