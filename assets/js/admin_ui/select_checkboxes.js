$().ready(function(){

	$("#select-all").parent().removeAttr("class");

	$("#select-all").click(function () {
		$("input[name='select[]']").attr("checked","checked");
		$(this).parent().attr("class","hide");
		$("#select-none").parent().removeAttr("class");

	});

	$("#select-none").click(function () {
		$("input[name='select[]']").removeAttr("checked");
		$(this).parent().attr("class","hide");
		$("#select-all").parent().removeAttr("class");

	});





});

