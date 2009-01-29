<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Objects</title>
</head>

<body>
	<h1>Objects</h1>
	<p>Objects are your content type templates. Click on a link to edit the template.</p>
	
	<ul><?php foreach($q as $index => $row) {?>
	<li><?php
		$uri = site_url("object/edit/".$row['object_id']);
		$text = $row['name'];
		$attr = array('title' => "edit '".$row['name']."' template");
	
		echo anchor($uri,$text,$attr);
	?></li>
	<?php } ?>
	</ul>

</body>
</html>
