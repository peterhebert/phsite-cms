<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Media browser</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script type="text/javascript" src="/assets/js/jquery-1.3.1.min.js"></script>
<script type="text/javascript" src="/assets/js/jqueryFileTree/jqueryFileTree.js"></script>
<script type="text/javascript">
//<![CDATA[

$(document).ready( function() {
    $('#file_browser').fileTree({
      root: '/media/originals',
      script: 'cms/media/build_filetree',
    }, function(file) {
        alert(file);
    });
});

// ]]>
</script>
<link href="/assets/js/jqueryFileTree/jqueryFileTree.css" rel="stylesheet" type="text/css" />
<style type="text/css">
/* <![CDATA[ */
#file_browser {
	border: 1px solid #666666;
	padding: 0.125em;
	float: left;
	width: 20em;
}
/* ]]> */
</style>

</head>
<body>
<div id="page">
	<div class="page-wrap block">
		<h1>Media browser</h1>
		<div id="file_browser"></div>
	</div>
</div>
</body>
</html>
