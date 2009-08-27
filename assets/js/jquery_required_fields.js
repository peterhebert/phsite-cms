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
