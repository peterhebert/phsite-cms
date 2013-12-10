tinyMCEPopup.requireLangPack();

var MediaBrowserDialog = {
	init : function() {

		var ed = tinyMCEPopup.editor;
	
		var f = document.forms[0]
		var n = ed.selection.getNode();
	
		var nm = ed.selection.getNode().nodeName;
		if(nm == 'IMG') {
			var file = ed.selection.getNode().src;
			 strip size from filename and convert to originals directory
	
			// ex. http://phsite.dev/media/cache/goalie_90.jpg	
			var orig = file.replace(/^http:\/\/([^\/]+)\/media\/cache\/(.*)_([0-9]+)\.([a-z]{2,5})$/gi, '/media/originals/$2.$4');
			var size = file.replace(/^http:\/\/([^\/]+)\/media\/cache\/(.*)_([0-9]+)\.([a-z]{2,5})$/gi, '$3');
	
			$('input#file').val(orig);
			$('#dimensions').val(size);
			openFile(orig);
		}
		else
		{
			var rel = file.replace(/^http:\/\/([^\/]+)\/media\/cache\/(.*)\.([a-z]{2,5})$/gi, '/media/originals/$2.$3');
			$('input#file').val(rel);
		}
	

	},

	insert : function(html) {
		// Insert the contents from the input into the document
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(MediaBrowserDialog.init, MediaBrowserDialog);
