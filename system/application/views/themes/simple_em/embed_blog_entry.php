<?php
if(isset($title)) 
{
	echo "<h3>{$title}</h3>\r";
}
if(isset($byline)) 
{
	echo "<p>{$byline}</p>\r";
}
if(isset($content_body)) 
{
	echo $content_body;
}

if(isset($content_keyword_links)) 
{
	echo "<p>{$content_keyword_links}</p>\r";
}


?>
