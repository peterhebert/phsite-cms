
	$().ready(function() {

		var dir = jQuery.url.attr("directory");
		var root = dir.replace(/^(.*)\/cms\/.*$/,'$1');
			
		$('textarea.richtext').tinymce({
			// Location of TinyMCE script
			script_url : root + '/assets/js/tinymce/jscripts/tiny_mce/tiny_mce.js',

		// General options
		theme : "advanced",
		plugins : "safari,xhtmlxtras,table,fullscreen,embed,media,mediabrowser",

		// Theme options
		theme_advanced_buttons1 : "formatselect,bold,italic,|,sub,sup,|,hr,charmap,|,image,mediabrowser,embed",
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
		extended_valid_elements: "object[data|classid|archive|codebase|width|height|name|usemap|type|codetype|standby|tabindex|declare],"
		+ "embed[src|type|allowscriptaccess|allowfullscreen|width|height|classid|width|height|codebase|*]"
		});
	});
