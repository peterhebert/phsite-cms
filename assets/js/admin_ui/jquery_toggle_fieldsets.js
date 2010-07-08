$().ready(function(){
	$("#container form fieldset legend:first-child").each(function() {
    	$(this).siblings().wrapAll('<div>');
	});

	
	$("#container form fieldset:gt(0)").addClass('closed');

	$("#container form fieldset legend").click(function() {
		$(this).parent().toggleClass('closed', 100);
		return false;
	});
	
	$(".panel:gt(0)").addClass('closed');

	$(".panel h1,.panel h2,.panel h3").click(function() {
		$(this).parent().toggleClass('closed', 100);
		return false;
	});



});

