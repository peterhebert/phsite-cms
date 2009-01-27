<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Object List</title>
</head>

<body>
	<h1>Object List</h1>
	<p>Here is a list of all objects in the database.</p>
	
	<ul><?php foreach($q as $index => $row) {?>
	<li><?php echo($row['object_id'].' : '.$row['name']); ?></li>
	<?php } ?>
	</ul>

</body>
</html>
