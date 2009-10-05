<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php
if(isset($html_header))
{
echo $html_header;
}
?>
</head>

<body>
<?php 
if(isset($params))
{
echo "<p>URI parameters:</p>\r";
echo "<pre>";
print_r($params);
echo "</pre>\r";

}
if(isset($msg))
{
echo($msg);
}
			if(isset($title))
			{
				echo "<h1>{$title}</h1>\r";
			}

?>

</body>
</html>
