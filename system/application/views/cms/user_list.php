<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Users | phSite</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/css/base.css" rel="stylesheet" type="text/css" />
<link href="/css/simple.css" rel="stylesheet" type="text/css" />
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
	<div class="page-wrap block" id="breadcrumb"><?php echo anchor("cms","phSite CMS"); ?> : Users</div>
	<div class="page-wrap block">
		<?php
		if(count($list) > 0) {
			echo ul($list);
		} else {
			echo "<p>No users found</p>";
		}
		?>
		<p><?php echo anchor("cms/user/edit","New User");  ?></p>
	</div>
	<div class="page-wrap block" id="footer"> phSite &copy; 2009 Peter Hebert. All rights reserved unless stated otherwise.</div>
</div>
</body>
</html>
