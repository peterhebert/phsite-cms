
$().ready(function(){

	/* jquery_external_links.js */
    $('a[rel="external"]').click( function() {
        window.open( $(this).attr('href') );
        return false;
    });
   
	/* jquery_toggle_fieldsets.js */
	$("#container form fieldset legend:first-child").each(function() {
    	$(this).siblings().wrapAll('<div>');
	});
	
	$("#container form fieldset:gt(0)").addClass('closed');

	$("#container form fieldset legend").click(function() {
		$(this).parent().toggleClass('closed', 100);
		return false;
	});
	
});



