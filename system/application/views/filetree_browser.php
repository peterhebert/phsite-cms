<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Jquery File Tree</title>
<link href="<?php echo BASE_PATH; ?>/assets/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="<?php echo BASE_PATH; ?>/assets/css/fonts.css" rel="stylesheet" type="text/css" media="all" />
<link href="<?php echo BASE_PATH; ?>/assets/css/grid.css" rel="stylesheet" type="text/css" media="screen" />
<link href="<?php echo BASE_PATH; ?>/assets/css/admin.css" rel="stylesheet" type="text/css" media="screen" />
<link href="<?php echo BASE_PATH; ?>/assets/css/jqueryFileTree.css" rel="stylesheet" type="text/css" />

<!-- TinyMCE popup -->
<script type="text/javascript" src="<?php echo BASE_PATH; ?>/assets/js/tinymce/jscripts/tiny_mce/tiny_mce_popup.js"></script>
<!-- JQuery File Tree -->
<script type="text/javascript" src="<?php echo BASE_PATH; ?>/assets/js/jquery-min.js"></script>
<script type="text/javascript" src="<?php echo BASE_PATH; ?>/assets/js/jquery-ui-min.js"></script>
<script type="text/javascript" src="<?php echo BASE_PATH; ?>/assets/js/jquery-easing.js"></script>
<script type="text/javascript" src="<?php echo BASE_PATH; ?>/assets/js/jqueryFileTree.js"></script>
<script type="text/javascript">
<!--
function openFile(filename)
{
	$('input#file').val(filename);
	$('#preview').html('');
	
	$.post("../image/ajax_preview", { file: filename },
		function(data){
		$('#preview').html(data);
	});
}

function doEmbed()
{

	$.post("../image/embed", { file: $('#file').val(), size: $('#dimensions').val() },
		function(data){
		MediaBrowserDialog.insert(data);

	});

}

$(document).ready( function() {
    $('#media_browser').fileTree({
        root: '/media/originals',
        script: '<?php echo BASE_PATH; ?>/filetree'
    }, function(file) {
        openFile(file);
    });
	
	$('#insert').click(function() {
		doEmbed();
	});


});

tinyMCEPopup.requireLangPack();

var MediaBrowserDialog = {
	init : function() {

		var ed = tinyMCEPopup.editor;
	
		var f = document.forms[0]
		var n = ed.selection.getNode();
	
		var nm = ed.selection.getNode().nodeName;

		if(nm == 'IMG') {
			var file = ed.selection.getNode().src;
			// strip size from filename and convert to originals directory
	
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




--></script>
</head>
<body>
<div class="padded" id="page">
	<div class="block margins">
		<div class="column w3">
			<h6>Files</h6>
			<div id="media_browser"></div>
		</div>
		<div class="float w4">
			<h6>Preview</h6>
			<div id="preview" class=" block bdr-all"></div>
			<form id="media-embed" action="<?php echo $this->uri->uri_string(); ?>" method="post">
				<p>
					<label for="file">File</label>
					<input id="file" type="text" class="input" />
				</p>
				<p>
					<label for="dimensions">Fit to size</label>
					<select id="dimensions" name="dimensions">
						<option value="<?php echo $img_size_th; ?>"><?php echo $cms_labels['img_th'].' ('.$img_size_th.' px)'; ?></option>
						<option value="<?php echo $img_size_sm; ?>"><?php echo $cms_labels['img_sm'].' ('.$img_size_sm.' px)'; ?></option>
						<option value="<?php echo $img_size_md; ?>"><?php echo $cms_labels['img_md'].' ('.$img_size_md.' px)'; ?></option>
						<option value="<?php echo $img_size_lg; ?>"><?php echo $cms_labels['img_lg'].' ('.$img_size_lg.' px)'; ?></option>
						<option value="0"><?php echo $cms_labels['img_full']; ?></option>
					</select>
				</p>

			</form>
			<div id="embed"></div>
		</div>
	</div>
		<div class="block">
			<div class="column w4">
				<input type="submit" id="insert" name="insert" value="<?php echo $cms_labels['insert'] ?>" />
			</div>

			<div  class="float w3 align-right">
				<input type="button" id="cancel" name="cancel" value="<?php echo $cms_labels['cancel'] ?>" onclick="tinyMCEPopup.close();" />
			</div>
		</div>

</div>
</body>
</html>
