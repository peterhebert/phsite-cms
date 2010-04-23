tinyMCE_GZ.init({
	plugins : 'style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras',
	themes : 'advanced',
	languages : 'en',
	disk_cache : true,
	debug : false,
	relative_urls : false,
	convert_urls: false
});

tinyMCE.init({
	mode : "textareas",
	theme : "advanced",
	plugins : "safari,xhtmlxtras,table,fullscreen",
	theme_advanced_buttons1 : "formatselect,bold,italic,|,sub,sup,|,hr,image,charmap",
	theme_advanced_buttons2 : "bullist,numlist,|,outdent,indent,|,blockquote,cite,abbr,acronym,|,ins,del",
	theme_advanced_buttons3 : "tablecontrols",
	theme_advanced_buttons4 : "cut,copy,paste,|,undo,redo,|,link,unlink,anchor,|,code,removeformat,cleanup,fullscreen",
	theme_advanced_toolbar_align : "left",
	editor_selector : "richtext",
	editor_deselector : "textarea",
	content_css : "/assets/css/tinymce.css",
	relative_urls : false,
	convert_urls : false
});

function toggleEditor(id) {
	if (!tinyMCE.getInstanceById(id))
		tinyMCE.execCommand('mceAddControl', false, id);
	else
		tinyMCE.execCommand('mceRemoveControl', false, id);
}
