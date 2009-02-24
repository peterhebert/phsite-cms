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
<div id="masthead">
	<div class="page-wrap block">
		<div class="column">
			<h2><a href="/">phSite</a></h2>
		</div>
		<div class="column lh">a content manager for the rest of us</div>
		<div class="float-right lh">
			<form action="<?php echo site_url("cms/content/search") ?>" method="post" id="site-search">
				<input name="search-text" id="search-text" type="text" />
				<input name="search-submit" type="submit" id="search-submit" value="Search" />
			</form>
		</div>
	</div>
</div>
<div id="page">
	<div class="page-wrap block" id="breadcrumb"><?php echo anchor("cms","phSite CMS"); ?> : CMS Home</div>
	<div class="page-wrap block">
		<div class="column w3"> <?php echo $content; ?>
<?php
			foreach($new_objects as $index => $row) {
				$my_uri = site_url("cms/content/edit/type/".$row['object_id']);
				$dropdown[$my_uri] = $row['name'];
			}
?>
<p>Create New <?php echo form_dropdown('new_content',$dropdown); ?> <input id="new_go" name="new_go" type="button" value="Go" /></p>



			
		</div>
		<div class="float w2">
			<h2 class="first">Data Structure</h2>
			<p>You can define what types of content your CMS stores. Your content types are called Objects, and the individual data fields are called Fields.</p>
			<p>Click on a link to edit the object or field.</p>
			<div class="block">
				<div class="column w1"> <?php echo $objects; ?> </div>
				<div class="float w1"> <?php echo $fields; ?> </div>
			</div>
		</div>
	</div>
	<div class="page-wrap block" id="footer"> phSite &copy; 2009 Peter Hebert. All rights reserved unless stated otherwise.</div>
</div>
</body>
</html>
