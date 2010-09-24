//	skin : "thebigreason",

/* jquery UI niceties */

$().ready(function(){

	/* jquery_change_type.js */
	$("form select[name='type_id']").change(function () {
	
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

   
	/* jquery_external_links.js */
    $('a[rel="external"]').click( function() {
        window.open( $(this).attr('href') );
        return false;
    });
   

	/* jquery_new_content.js */
	$("#new_go").click(function () {
	
		var myloc = $("select[name='new_content']").val();
		window.location = myloc ;
		
	});

	

	/* jquery_required_fields.js */
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


	/* jquery_toggle_fieldsets.js */
	$("#container form fieldset legend:first-child").each(function() {
    	$(this).siblings().wrapAll('<div>');
	});

	
	//$("#container form fieldset:gt(0)").addClass('closed');

	$("#container form fieldset legend").click(function() {
		$(this).parent().toggleClass('closed', 100);
		return false;
	});
	
	$(".panel:gt(0)").addClass('closed');

	$(".panel h1,.panel h2,.panel h3").click(function() {
		$(this).parent().toggleClass('closed', 100);
		return false;
	});

	/* Jquery / TinyMCE integration */
	var dir = jQuery.url.attr("directory");
	var root = dir.replace(/^(.*)\/cms\/.*$/,'$1');
	var RTEwidth = $('textarea.richtext').width();
	
	if (RTEwidth < 350) {
		RTEwidth = '31em';
	}
		
	$('textarea.richtext').tinymce({
		// Location of TinyMCE script
		script_url : root + '/assets/js/tinymce/jscripts/tiny_mce/tiny_mce.js',

		// General options
		theme : "advanced",
		plugins : "safari,xhtmlxtras,table,fullscreen,embed,mediabrowser",
		skin : "morereasons",
		width : RTEwidth,
	
		// Theme options
		theme_advanced_buttons1 : "formatselect,bold,italic,|,sub,sup,|,hr,charmap,|,mediabrowser,embed",
		theme_advanced_buttons2 : "blockquote,cite,abbr,acronym,|,bullist,numlist,|,outdent,indent,|,link,unlink,anchor",
		theme_advanced_buttons3 : "tablecontrols",
		theme_advanced_buttons4 : "cut,copy,paste,|,undo,redo,|,code,removeformat,cleanup,fullscreen",
		theme_advanced_toolbar_align : "left",
	
		// Example content CSS (should be your site CSS)
		content_css : root + "/assets/css/tinymce.css",
	
		// misc settings
		relative_urls : false,
		convert_urls : false,
		apply_source_formatting : true,
		extended_valid_elements: "object[data|classid|archive|codebase|width|height|name|usemap|type|codetype|standby|tabindex|declare|*],"
		+ "embed[src|type|allowscriptaccess|allowfullscreen|width|height|classid|width|height|codebase|*],"
		+ "audio[accesskey|autoplay|class|controls|height|id|lang|loop|preload|src|style|tabindex|title|width|*],"
		+ "video[accesskey|autoplay|class|controls|height|id|lang|loop|preload|src|style|tabindex|title|width|*]"
	});


	/* select_checkboxes.js */
/*	$("#select-all").parent().removeAttr("class");

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
*/


});



