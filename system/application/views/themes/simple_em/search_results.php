<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Search Results</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/assets/css/base.css" rel="stylesheet" type="text/css" />
<link href="/assets/css/simple.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block" id="breadcrumb"><?php echo anchor("","Home"); ?> : Search Results</div>
	<div class="page-wrap block">
		<h1>Search Results</h1>
		<?php
		echo "<p>your search term was: <strong>". $term."</strong></p>\n";
		if(count($matches) > 0 ) {
			foreach ($matches as $result) {
				$list[] = anchor("cms/content/edit/id/".$result['content_id'], $result['content_id'])." (".$result['score'].")";
			}
			echo ul($list);
		} else {
		echo "<p>no results found</p>\n";
		}
		?>
	</div>
	<div class="page-wrap block" id="footer">footer</div>
</div>
</body>
</html>
