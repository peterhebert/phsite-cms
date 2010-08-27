$().ready(function() {
	

	$("form select[name='field_input_type']").change(function() {
															  

		if( $(this).val() != 'list' ) {
			$("form select[name='list_id']").val(0);
			
			$('#field_allow_multiple_0').attr('checked', true);


		}

	});

});
