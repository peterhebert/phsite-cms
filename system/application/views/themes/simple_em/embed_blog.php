<?php
foreach($blog as $entry)
{

	if(array_key_exists('title',$entry) && $entry['title'] != '') 
	{
		$title = $entry['title'];
		if(array_key_exists('permalink',$entry)) 
		{
			$pl_title = sprintf($cms_labels['permalink_title'],$entry['title']);
			$title = anchor($entry['permalink'], $entry['title'], array('title' => $pl_title));
	
		}
		echo "<h3>".$title."</h3>\r";
	}

	if(array_key_exists('subtitle',$entry) && $entry['subtitle'] != '') 
	{
		echo "<h4>{$subtitle}</h4>\r";
	}
	
	if(array_key_exists('author',$entry)) 
	{
		$user_name_formatted = $entry['author']['name_first'].' '.$entry['author']['name_last'];
		$byline = sprintf($cms_labels['posted_by'],$entry['date_created'],$user_name_formatted);
		echo "<p>{$byline}</p>\r";
	}
	
	if(array_key_exists('content_excerpt',$entry)) 
	{
		echo $entry['content_excerpt'];
	}
	
	if(array_key_exists('content_keyword_links',$entry)) 
	{
		echo "<p>".$cms_labels['keywords']." : ".$entry['content_keyword_links']."</p>\r";
	}

	if(array_key_exists('permalink',$entry)) 
	{
		$pl_title = sprintf($cms_labels['permalink_title'],$entry['title']);
		
		$permalink = anchor($entry['permalink'], $cms_labels['read_more'], array('title' => $pl_title));
		echo "<p>{$permalink}</p>\r";
	}
	
	
}
?>
