<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Content</title>
</head>

<body>
	<h1>Content</h1>
	<p>Here is a list of all content in the database. Click a link to edit the content.</p>

<?php 
	$this->table->set_heading('Content ID', 'Parent ID', 'Object ID', 'Created', 'Updated');
	echo $this->table->generate($q); ?>
</body>
</html>
