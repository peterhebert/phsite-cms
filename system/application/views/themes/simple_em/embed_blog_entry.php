<?php
if(isset($title)) 
{
	echo "<h3>{$title}</h3>\r";
}
if(isset($byline)) 
{
	echo "<p>{$byline}</p>\r";
}

// excerpt or full entry?
if($mode == 'full') 
{
	if(isset($content_body)) 
	{
		echo $content_body;
	
	}
}
else
{
	if(isset($content_excerpt)) 
	{
		echo $content_excerpt;
	}
	if(isset($permalink)) 
	{
		echo "<p>{$permalink}</p>\r";
	}
}

// keywords

if(isset($content_keyword_links)) 
{
	echo "<p>{$content_keyword_links}</p>\r";
}

?>
