<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Field List</title>
</head>

<body>
	<h1>Field List</h1>
	<p>Here is a list of all fields in the database.</p>
	<?php echo $table; ?>
	<hr />
	
	<ul><?php foreach($q as $index => $row) {?>
	<li><?php echo($row['field_id'].' : '.$row['name']); ?></li>
	<?php } ?>
	</ul>

</body>
</html>
