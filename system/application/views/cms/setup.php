<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Setup | phSite</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/css/base.css" rel="stylesheet" type="text/css" />
<link href="/css/simple.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery-1.3.1.min.js"></script>

<script type="text/javascript">
/* <![CDATA[ */
$().ready(function(){

	$("#new_go").click(function () {
	
		var myloc = $("select[name='new_content']").val();
		window.location = myloc ;
		
	});

});
/* ]]> */
</script>
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb"><?php echo anchor("cms","phSite CMS"); ?> : Setup</div>
	<div class="page-wrap block">
		<h1>Setup</h1>
		<div class="column w2">
			<h2 class="first">Data Structure</h2>
			<p>You can define what types of content your CMS stores. Your content types are called Objects, and the individual data fields are called Fields. Fields can be assinged to any object.</p>
			<div class="block">
				<div class="column w1"><?php echo $objects; ?></div>
				<div class="float w1"><?php echo $fields; ?></div>
			</div>
		</div>
		<div class="column w1">
			<?php echo $languages; ?>
		</div>
		<div class="column w1">
			<?php echo $users; ?>
		</div>

	</div>
	<div class="page-wrap block" id="footer"><?php require_once('footer.php'); ?></div>
</div>
</body>
</html>
