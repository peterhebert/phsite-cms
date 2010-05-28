<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Jquery File Tree</title>

<link href="<?php echo BASE_PATH; ?>/assets/css/base.css" type="text/css" rel="stylesheet" />	
<link href="<?php echo BASE_PATH; ?>/assets/css/simple.css" type="text/css" rel="stylesheet" />	
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
	
	$.post("./image/ajax_preview", { file: $('#file').val() },
		function(data){
		$('#preview').html(data);
	});
}

function doEmbed()
{
	$.post("./image/embed", { file: $('#file').val(), size: $('#dimensions').val() },
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

	$('#calc').click(function() {
		doEmbed();
	});
	

});

--></script>
<link href="/assets/css/base.css" rel="stylesheet" type="text/css" />
<link href="/assets/css/simple.css" rel="stylesheet" type="text/css" />
</head>
<body>
	<div class="padded" id="page">
		<div class="block">
			<div class="column w4">
				<form id="testform" action="<?php echo $this->uri->uri_string(); ?>" method="post">
					<p><label for="file">File</label>
					<input id="file" type="text" class="input" />
					</p>
					<p><label for="dimensions">Fit to size</label>
					<select id="dimensions" name="dimensions">
						<option value="<?php echo $img_size_th; ?>"><?php echo $cms_labels['img_th'].' ('.$img_size_th.' px)'; ?></option>
						<option value="<?php echo $img_size_sm; ?>"><?php echo $cms_labels['img_sm'].' ('.$img_size_sm.' px)'; ?></option>
						<option value="<?php echo $img_size_md; ?>"><?php echo $cms_labels['img_md'].' ('.$img_size_md.' px)'; ?></option>
						<option value="<?php echo $img_size_lg; ?>"><?php echo $cms_labels['img_lg'].' ('.$img_size_lg.' px)'; ?></option>
					</select></p>
					<p><a id="calc" href="#">Generate Embed code</a></p>
				</form>
				<div id="embed"></div>
			</div>
			<div class="float w3">
				<h6>Preview</h6>
				<div id="preview" class=" block bdr-all"></div>
				<h6>Files</h6>
				<div id="files" class="bdr-all"></div>
			</div>

		</div>
	</div>

</body>
</html>
