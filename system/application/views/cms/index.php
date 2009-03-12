<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>CMS Home | phSite</title>
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
	<div class="page-wrap block" id="breadcrumb"><?php echo anchor("cms","phSite CMS"); ?> : CMS Home</div>
	<div class="page-wrap block">
		<div class="column w4"> <?php echo $content; ?>
			<?php
			foreach($new_objects as $index => $row) {
				$my_uri = site_url("cms/content/edit/type/".$row['object_id']);
				$dropdown[$my_uri] = $row['name'];
			}
			?>
			<p>Create New <?php echo form_dropdown('new_content',$dropdown); ?> <input id="new_go" name="new_go" type="button" value="Go" /></p>
		</div>
		<div class="float w2"><?php require_once('setup_menu.php'); ?></div>
	</div>
	<div class="page-wrap block" id="footer"><?php require_once('footer.php'); ?></div>
</div>
</body>
</html>
