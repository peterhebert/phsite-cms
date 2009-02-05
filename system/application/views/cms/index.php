<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>CMS Home | phSite</title>
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
<div class="page-wrap block" id="breadcrumb"><a href="#">phSite CMS</a> : <a href="#">Section</a> : <a href="#">Page</a></div>
<div class="page-wrap block" id="page">
	<div class="column w3">
		<h2 class="first">Recent Entries</h2>
		<p>Nisl diam commodo ad eu dolor commodo esse in ut nulla aliquip te eu nulla facilisi dignissim velit. Nostrud quis, facilisis veniam vel nibh, sed vero eros consequat facilisi magna eros ad luptatum vel. Duis odio volutpat. Enim enim laoreet euismod et et facilisi autem ut iusto quis lobortis eu adipiscing. Dolore ad lobortis, quis dolore consequat qui, quis facilisis autem nulla ad. </p>
		<p>Nisl consequat suscipit. Luptatum veniam velit. </p>
		<p>Duis, eu vel. Dolore quis autem, dolor eros quis in augue aliquam hendrerit lobortis et vel hendrerit. Vero eu tation, dignissim iriure consectetuer iusto ullamcorper molestie te wisi esse ex qui, ut esse ut magna. Enim praesent in diam quis praesent lobortis lorem eros nibh consequat tation commodo suscipit, ad, ut augue duis sed. Enim tation duis ex. </p>
		<p>Iusto duis consequat, ut. Vel aliquip, qui, feugiat odio odio exerci vero, illum duis dolor consectetuer eum, luptatum amet iusto ullamcorper odio iriure blandit. </p>
	</div>
	<div class="float w2">
		<h2 class="first">Data Structure</h2>
		<p>You can define what types of content your CMS stores. Your content types are called Objects, and the individual data fields are called Fields.</p>
<p>Click on a link to edit the object or field.</p>
		<div class="block">
			<div class="column w1">
				<?php echo $objects; ?>
			</div>
			<div class="float w1">
				<?php echo $fields; ?>
			</div>
		</div>
	</div>
	
</div>
<div class="page-wrap block" id="footer"> phSite &copy; 2009 Peter Hebert. All rights reserved unless stated otherwise.</div>
</body>
</html>
