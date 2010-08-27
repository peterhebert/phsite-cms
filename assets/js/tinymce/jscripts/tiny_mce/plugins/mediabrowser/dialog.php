<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>{#mediabrowser_dlg.title}</title>

	<link href="../../../../../../css/reset.css" rel="stylesheet" type="text/css" media="all" />
	<link href="../../../../../../css/fonts.css" rel="stylesheet" type="text/css" media="all" />
	<link href="../../../../../../css/grid.css" rel="stylesheet" type="text/css" media="screen" />
	<link href="../../../../../../css/admin.css" rel="stylesheet" type="text/css" media="screen" />
	<link href="../../../../../../css/jqueryFileTree.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript" src="../../tiny_mce_popup.js"></script>

	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/jquery-ui.min.js"></script>
	<script type="text/javascript" src="/assets/js/jquery-easing.js"></script>
	<script type="text/javascript" src="/assets/js/jqueryFileTree.js"></script>
	<script type="text/javascript" src="js/dialog.js"></script>
</head>
<body>

<div class="padded" id="page">
	<div class="block">
	<p>{#mediabrowser_dlg.title}</p>
		<div class="column w3">
			<h6>{#mediabrowser_dlg.preview}</h6>
			<div id="preview" class=" block bdr-all"></div>
			<h6>{#mediabrowser_dlg.files}</h6>
			<div id="files" class="bdr-all"></div>
		</div>
		<div class="float w4">
			<form id="media-embed" onsubmit="MediaBrowserDialog.insert();return false;" action="#">
				<p>
					<label for="file">{#mediabrowser_dlg.file}</label>
					<input id="file" type="text" class="input" />
				</p>
				<p>
					<label for="dimensions">{#mediabrowser_dlg.dimensions}</label>
					<select id="dimensions" name="dimensions">
						<option value="th">{#mediabrowser_dlg.img_th}</option>
						<option value="sm">{#mediabrowser_dlg.img_sm}</option>
						<option value="md">{#mediabrowser_dlg.img_md}</option>
						<option value="lg">{#mediabrowser_dlg.img_lg}</option>
					</select>
				</p>
				<p><a id="calc" href="#">{#mediabrowser_dlg.generate}</a></p>
			</form>
			<div id="embed"></div>
		</div>
	</div>
		<div class="block">
			<div class="column w4">
				<input type="submit" id="insert" name="insert" value="{#insert}" />
			</div>

			<div  class="float w3 align-right">
				<input type="button" id="cancel" name="cancel" value="{#cancel}" onclick="tinyMCEPopup.close();" />
			</div>
		</div>

</div>



</body>
</html>
