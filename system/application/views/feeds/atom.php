<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">

	<title><?php echo $feed['title']; ?></title>
	<link href="<?php echo $feed['uri']; ?>"/>
<?php
if(isset($feed['atom_id']) && $feed['atom_id'] != '')
{
	echo "\t<id>{$feed['atom_id']}</id>\r";
}

// feed author
if(isset($feed['author']) && is_array($feed['author']))
{
	echo "\t<author>\r";
	echo "\t\t<name>{$feed['author']['name']}</name>\r";
	echo "\t\t<email>{$feed['author']['email']}</email>\r";
	echo "\t</author>\r";
}
else if(isset($feed['editor']) && is_array($feed['editor']))
{
	echo "\t<author>\r";
	echo "\t\t<name>{$feed['editor']['name']}</name>\r";
	echo "\t\t<email>{$feed['editor']['email']}</email>\r";
	echo "\t</author>\r";
}
else
{
}

// publish date
if(isset($feed['date_created']) && $feed['date_created'] != '')
{
	echo "\t<published>".$feed['date_created']."</published>\r";
}

// last updated
if(isset($feed['date_updated']) && $feed['date_updated'] != '')
{
	echo "\t<updated>".$feed['date_updated']."</updated>\r";
}

if(isset($items))
{
	foreach ($items as $item)
	{
		echo "\t<entry>\r";
		echo "\t\t<title>".$item['title']."</title>\r";

		// description
		if(isset($item['description']) && $item['description'] != '')
		{
			echo "\t\t<summary><![CDATA[".$item['description']."]]></summary>\r";
		}

		if(isset($item['body']) && $item['body'] != '')
		{
			echo "\t\t<content type=\"html\"><![CDATA[".$item['body']."]]></content>\r";
		}

		// item URI
		if(isset($item['uri']) && $item['uri'] != '')
		{
			echo "\t\t<link href=\"{$item['uri']}\"/>\r";
		}
		// author
		if(isset($item['author']) && is_array($item['author']))
		{
			echo "\t\t<author>{$item['author']['email']} ({$item['author']['name']})</author>\r";
		}

		// publish date
		if(isset($item['date_created']) && $item['date_created'] != '')
		{
			echo "\t\t<published>".$item['date_created']."</published>\r";
		}
		// UPDATED date
		if(isset($item['date_updated']) && $item['date_updated'] != '')
		{
			echo "\t\t<updated>".$item['date_updated']."</updated>\r";
		}

		// enclosures
		if(isset($item['attachments']) && is_array($item['attachments']))
		{
			foreach($item['attachments'] as $file)
			{
				echo "<link rel=\"enclosure\" href=\"{$file['uri']}\" length=\"{$file['filesize_bytes']}\" type=\"{$file['mime_type']}\" title=\"{$file['title']}\"/>\r";
			}
		}

		/// atom GUID
		if(isset($item['atom_id']) && $item['atom_id'] != '')
		{
			echo "\t<id>{$item['atom_id']}</id>\r";
		}
		echo "\t</entry>\r";

	}
}
?>

</feed>
