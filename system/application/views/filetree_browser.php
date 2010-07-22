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

<!-- JQuery File Tree -->
<script type="text/javascript" src="<?php echo BASE_PATH; ?>/assets/js/jquery-min.js"></script>
<script type="text/javascript" src="<?php echo BASE_PATH; ?>/assets/js/jquery-ui-min.js"></script>
<script type="text/javascript" src="<?php echo BASE_PATH; ?>/assets/js/jquery-easing.js"></script>
<script type="text/javascript" src="<?php echo BASE_PATH; ?>/assets/js/jqueryFileTree.js"></script>
<script type="text/javascript">
<!--

function openFile(file)
{
	$('input#file').val(file);
	
	$.post("../image/ajax_preview", { file: $('#file').val() },
		function(data){
		$('#preview').html(data);
	});
}

function doEmbed()
{

		tinyMCEPopup.editor.execCommand('mceInsertContent', false, 'inserted code');

		tinyMCEPopup.close();



	$.post("../image/embed", { file: $('#file').val(), size: $('#dimensions').val() },
		function(data){
		$('#embed').html(data);

	});

}

$().ready(function(){



	$('#files').fileTree({
        root: '<?php echo $filetree_root; ?>',
        script: '/filetree'
    }, function(file) {
        openFile(file);
    });

	$('#media-embed').submit(function() {
		doEmbed();
	});

/*
	$('#calc').click(function() {
		doEmbed();
	});
	
	$('#insert').click(function() {
	  alert('you clicked the Insert button');
	  return false;
	});

*///onmousedown="$('#content').tinymce().execCommand('mceInsertContent',false,'<b>Hello world!!</b>');"

});

--></script>
</head>
<body>
<div class="padded" id="page">
	<div class="block">
		<div class="column w3">
			<h6>Preview</h6>
			<div id="preview" class=" block bdr-all"></div>
			<h6>Files</h6>
			<div id="files" class="bdr-all"></div>
		</div>
		<div class="float w4">
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
					</select>
				</p>
				<p><a id="calc" href="#">Generate Embed code</a></p>
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
