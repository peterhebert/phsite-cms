<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="<?php echo $theme_path; ?>base.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $theme_path; ?>simple_em.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
<div class="page-wrap golden block">
	<div class="column g1">
		<h1><?php echo $title; ?></h1>
		<?php echo if(isset($subtitle)) { ?>
		<h2><?php echo $subtitle ?></h2>
		<?php }
		
		
		?>
		
		
		
		<h3>Post Title</h3>
		<p>Nisl diam commodo ad eu dolor commodo esse in ut nulla aliquip te eu nulla facilisi dignissim velit. Nostrud quis, facilisis veniam vel nibh, sed vero eros consequat facilisi magna eros ad luptatum vel. Duis odio volutpat. Enim enim laoreet euismod et et facilisi autem ut iusto quis lobortis eu adipiscing. Dolore ad lobortis, quis dolore consequat qui, quis facilisis autem nulla ad. </p>
		<p>Posted 2009-01-07  10:45 by John Doe | <a href="#">More</a></p>

		<p><a href="#">Newer Posts</a> | <a href="#">Older Posts</a> | <a href="#">Archive</a></p>
	</div>
	<div class="float g2">
		<h3><?php echo $title; ?></h3>
		<?php echo $content_data['body']; ?>
		<h3>Tags</h3>
		<ul>
			<li><strong><a href="#">XHTML</a></strong> (25)<br />
			</li>
			<li><strong><a href="#">CSS</a></strong> (4)<br />
			</li>
			<li><strong><a href="#">PHP</a></strong> (32)</li>
			<li><strong><a href="#">ASP</a></strong> (25)</li>
			<li><strong><a href="#">AJAX</a></strong> (25)<br />
			</li>
			<li><strong><a href="#">Design</a></strong> (4)<br />
			</li>
			<li><strong><a href="#">Video</a></strong> (32)</li>
			<li><strong><a href="#">Other</a></strong> (25)</li>
		</ul>
		<h3>Most Commented</h3>
		<ul>
			<li><strong><a href="#">Post Title</a></strong> (25)<br />
				John Doe<br />
				2009-01-07  10:45 </li>
			<li><strong><a href="#">Post Title</a></strong> (25)<br />
				John Doe<br />
				2009-01-07  10:45</li>
			<li><strong><a href="#">Post Title</a></strong> (25)<br />
				John Doe<br />
				2009-01-07  10:45</li>
			<li> <strong><a href="#">Post Title</a></strong> (25)<br />
				John Doe<br />
				2009-01-07  10:45 </li>
		</ul>
	</div>
</div>
<div class="page-wrap block" id="footer"> &copy; 1996-2009 Peter Hebert. All rights reserved unless stated otherwise.</div>
</div>
</body>
</html>
