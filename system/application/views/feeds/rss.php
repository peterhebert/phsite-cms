<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
	<channel>
		<title><?php echo $feed['title']; ?></title>
		<link><?php echo $feed['uri']; ?></link>
<?php
// copyright
if(isset($feed['description']) && $feed['description'] != '')
{
	echo "<description><![CDATA[".$feed['description']."]]></description>\r";
}
// language
if(isset($feed['language']) && $feed['language'] != '')
{
	echo "\t\t<language>".$feed['language']."</language>\r";
}
// copyright
if(isset($feed['copyright']) && $feed['copyright'] != '')
{
	echo "\t\t<copyright><![CDATA[".$feed['copyright']."]]></copyright>\r";
}
// publish date
if(isset($feed['date_created']) && $feed['date_created'] != '')
{
	echo "\t\t<pubDate>".$feed['date_created']."</pubDate>\r";
}
// last updated
if(isset($feed['date_updated']) && $feed['date_updated'] != '')
{
	echo "\t\t<lastBuildDate>".$feed['date_updated']."</lastBuildDate>\r";
}
// editor
if(isset($feed['editor']) && is_array($feed['editor']))
{
	echo "\t\t<managingEditor>{$feed['editor']['email']} ({$feed['editor']['name']})</managingEditor>\r";
}
// webmaster
if(isset($feed['webmaster']) && is_array($feed['webmaster']))
{
	echo "\t\t<webMaster>{$feed['webmaster']['email']} ({$feed['webmaster']['name']})</webMaster>\r";
}
// generator
if(isset($cms_labels['phsite_cms']))
{
	echo "\t\t<generator>".$cms_labels['phsite_cms']."</generator>\r";
}
echo "\t\t<docs>http://cyber.law.harvard.edu/rss/rss.html</docs>\r";
// feed image
if(isset($feed['image']) && is_array($feed['image']))
{
	echo "\t\t<image>\r";
	echo "\t\t\t<url>{$feed['image']['uri']}</url>\r";
	echo "\t\t\t<title>{$feed['image']['title']}</title>\r";
	echo "\t\t\t<link>{$feed['uri']}</link>\r";
	echo "\t\t\t<width>{$feed['image']['width']}</width>\r";
	echo "\t\t\t<height>{$feed['image']['height']}</height>\r";
	echo "\t\t</image>\r";
}

if(isset($items))
{
	foreach ($items as $item)
	{
		echo "\t\t<item>\r";
		echo "\t\t\t<title>".$item['title']."</title>\r";

		// description
		if(isset($item['description']) && $item['description'] != '')
		{
			echo "\t\t\t<description><![CDATA[".$item['description']."]]></description>\r";
		}
		else if(isset($item['excerpt']) && $item['excerpt'] != '')
		{
			echo "\t\t\t<description><![CDATA[".$item['excerpt']."]]></description>\r";
		}
		else if(isset($item['body']) && $item['body'] != '')
		{
			$body = strip_tags($item['body']);
			$excerpt = excerpt($body,100);
			echo "\t\t\t<description><![CDATA[".$excerpt."]]></description>\r";
		}
		else
		{
		}
		// item URI
		if(isset($item['uri']) && $item['uri'] != '')
		{
			echo "\t\t\t<guid>".$item['uri']."</guid>\r";
		}
		// author
		if(isset($item['author']) && is_array($item['author']))
		{
			echo "\t\t\t<author>{$item['author']['email']} ({$item['author']['name']})</author>\r";
		}

		// publish date
		if(isset($item['date_created']) && $item['date_created'] != '')
		{
			echo "\t\t\t<pubDate>".$item['date_created']."</pubDate>\r";
		}
		// item image
		if(isset($item['image']) && is_array($item['image']))
		{
			echo "\t\t<image>\r";
			echo "\t\t\t<url>{$item['image']['uri']}</url>\r";
			echo "\t\t\t<title>{$item['image']['title']}</title>\r";
			echo "\t\t\t<link>{$item['uri']}</link>\r";
			echo "\t\t\t<width>{$item['image']['width']}</width>\r";
			echo "\t\t\t<height>{$item['image']['height']}</height>\r";
			echo "\t\t</image>\r";
		}
		// enclosures
		if(isset($item['attachments']) && count($item['attachments']) > 0)
		{
			$file = array_shift($item['attachments']);
			echo "\t\t\t<enclosure url=\"{$file['uri']}\" length=\"{$file['filesize_bytes']}\" type=\"{$file['mime_type']}\" />\r";
		}

		echo"\t\t</item>\r";
	}
}
?>
</channel>
</rss>