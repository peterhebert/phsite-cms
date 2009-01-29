<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Fields</title>
</head>

<body>
	<h1>Fields</h1>
	<p>Here is a list of all fields in the database. Click a link to edit the field.</p>
	<p><?php echo anchor(site_url("cms/field/edit"),"New field");  ?></p>
	<ul><?php foreach($q as $index => $row) {?>
	
	<li><?php
		$uri = site_url("cms/field/edit/".$row['field_id']);
		$text = $row['name'];
		$attr = array('title' => "edit '".$row['name']."' field");
	
		echo anchor($uri,$text,$attr);
	?></li>
	
	
	<?php } ?>
	</ul>

</body>
</html>
