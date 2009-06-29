<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Object editor | phSite</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/assets/css/base.css" rel="stylesheet" type="text/css" />
<link href="/assets/css/simple.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/assets/js/jquery-1.3.1.min.js"></script>
<script type="text/javascript">
/* <![CDATA[ */
$().ready(function(){
   
    $('a[rel="external"]').click( function() {
        window.open( $(this).attr('href') );
        return false;
    });
   
});

/* ]]> */
</script>
<script type="text/javascript">
/* <![CDATA[ */
$().ready(function() {
	

	$("form input[name='required[]']").click(function() {
	
		if( $(this).attr('checked') == true ) {
			$(this).parent().siblings(1).children(0).attr("checked",true);
		}
		
	});

	$("form input[name='exists[]']").click(function() {

		if( $(this).attr('checked') == false ) {
			$(this).parent().siblings(1).children(0).attr("checked",false);

			var myIndex = $(this).parent().siblings(1).children(0).val();
			var OrderMatch = "form input[name='order["+myIndex+"]']";
			$(OrderMatch).val("");

		}

	});

});

/* ]]> */
</script>
</head>
<body>
<?php //require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb"><?php echo $breadcrumb; ?></div>
	<div class="page-wrap block">

			<h1 class="first">Content type editor</h1>
			<?php 
			if (isset($msg) && $msg != ''){
				echo "<div class=\"msg\">{$msg}</div>\n";
			}

			if (isset($fields) && count($fields) > 0) {
				echo "<pre>";
				print_r($fields);
				echo "</pre>\r";
			}

			if (isset($form) && $form != '') {
				echo $form;
			}

?>
	</div>
	<div class="page-wrap block" id="footer"><?php require_once('footer.php'); ?></div>
</div>
</body>
</html>
