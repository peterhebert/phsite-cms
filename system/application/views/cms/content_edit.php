<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Content editor | phSite</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/assets/css/base.css" rel="stylesheet" type="text/css" />
<link href="/assets/css/simple.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/assets/js/jquery-1.3.1.min.js"></script>
<script type="text/javascript">
/* <![CDATA[ */
$().ready(function(){

	$("form select[name='object_id']").change(function () {
	
		var myloc = $("form input[name='my_url']").val();
		var myval = $(this).val();
		var newloc = myloc + '/type/' + myval;
		window.location = newloc ;
		
	});

});
/* ]]> */
</script>
<script type="text/javascript" src="/assets/js/tiny_mce/tiny_mce_gzip.js"></script>
<script type="text/javascript">
/* <![CDATA[ */

tinyMCE_GZ.init({
	plugins : 'style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras',
	themes : 'advanced',
	languages : 'en',
	disk_cache : true,
	debug : false
});

tinyMCE.init({
	mode : "textareas",
	theme : "advanced",
	plugins : "safari,xhtmlxtras,table,fullscreen",
	theme_advanced_buttons1 : "formatselect,bold,italic,|,sub,sup,|,hr,image,charmap",
	theme_advanced_buttons2 : "bullist,numlist,|,outdent,indent,|,blockquote,cite,abbr,acronym,ins,del",
	theme_advanced_buttons3 : "tablecontrols",
	theme_advanced_buttons4 : "cut,copy,paste,|,undo,redo,|,link,unlink,anchor,|,code,removeformat,cleanup,fullscreen",
	theme_advanced_toolbar_align : "left",
	editor_selector : "richtext",
	editor_deselector : "textarea",
	content_css : "/css/tinymce.css"

});


function toggleEditor(id) {
	if (!tinyMCE.getInstanceById(id))
		tinyMCE.execCommand('mceAddControl', false, id);
	else
		tinyMCE.execCommand('mceRemoveControl', false, id);
}
/* ]]> */
</script>

</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb"><?php echo anchor("cms","phSite CMS"); ?> : Content editor</div>
	<div class="page-wrap golden block">
		<h1>Content editor</h1>
		<?php
		if(isset($msg)) {
			echo"<div class=\"msg\">";
			echo $msg;
			echo"</div>\n";
		}
		if(isset($uri)) {
			echo "<p>".anchor($uri,"Preview this page", array('rel' => 'external','title' => 'Preview this page in a new browser window'))."</p>\n";
		}
		// open form
		echo form_open($action);

		echo form_hidden('content_id', $content_id);
		echo form_hidden('title_id', $title_id);
		echo form_hidden('my_url', $action);

		// do main content fields
		?>
		<div class="column g1"><fieldset><legend>Content</legend>
		<?php echo $content; ?>
		</fieldset></div>
		<div class="float g2"><fieldset><legend>Meta</legend>
		<?php echo $meta; ?>
		</fieldset></div>
		<?php


		// close form
		echo "<p>";
		echo form_submit('submit', 'Save');
		echo "</p>\n";

		echo form_close();
		?>
	</div>
	<div class="page-wrap block" id="footer"><?php require_once('footer.php'); ?></div>
</div>
</body>
</html>
