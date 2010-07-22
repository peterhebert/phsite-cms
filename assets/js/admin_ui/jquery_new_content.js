$().ready(function(){

	$("#new_go").click(function () {
	
		var myloc = $("select[name='new_content']").val();
		window.location = myloc ;
		
	});

});
