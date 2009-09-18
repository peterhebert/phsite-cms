$().ready(function() {
	

	$("form input[name='required[]']").click(function() {
	
		if( $(this).attr('checked') == true ) {
			$(this).parent().siblings(1).children(0).attr("checked",true);
		}
		
	});

	$("form input[name='exists[]']").click(function() {

		if( $(this).attr('checked') == false ) {

			$(this).parent().siblings().next().next().children(0).attr("checked",false);
			$(this).parent().siblings().next().next().next().children(0).val("");
		}

	});

});
