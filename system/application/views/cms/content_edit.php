<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Content editor | phSite</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/css/base.css" rel="stylesheet" type="text/css" />
<link href="/css/simple.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery-1.3.1.min.js"></script>

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
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb"><?php echo anchor("cms","phSite CMS"); ?> : Content editor</div>
	<div class="page-wrap block">
		<div class="column w4">
			<h1>Content editor</h1>
			<?php
			if(isset($msg)) {
				echo "<div class=\"msg\">{$msg}</div>";
			}

			if(isset($form)) {
				echo($form);
			}
			
			?>
		</div>
		<div class="float w1">
			<?php
			if(isset($otherlangs) && count($otherlangs) > 0) {
				echo "<h4 class=\"first\">Other Languages</h4>\n";
				echo ul($otherlangs);
			}
			?>
		</div>
	</div>
	<div class="page-wrap block" id="footer"><?php require_once('footer.php'); ?></div>
</div>
</body>
</html>
